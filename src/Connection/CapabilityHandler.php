<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\CAP;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class CapabilityHandler implements ComponentInterface
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * @var array
	 */
	protected $availableCapabilities = [];

	/**
	 * @var array
	 */
	protected $capabilitiesToRequest = [];

	/**
	 * @var array
	 */
	protected $acknowledgedCapabilities = [];

	/**
	 * @var array
	 */
	protected $notAcknowledgedCapabilities = [];

	/**
	 * @var SASL
	 */
	protected $sasl;

	/**
	 * CapabilityHandler constructor.
	 *
	 * @param ComponentContainer $container
	 * @param SASL $SASL
	 */
	public function __construct(ComponentContainer $container, SASL $SASL)
	{
		$eventEmitter = EventEmitter::fromContainer($container);
		$eventEmitter->on('stream.created', [$this, 'initNegotiation']);
		$eventEmitter->on('irc.line.in.cap', [$this, 'responseRouter']);
		$eventEmitter->on('irc.cap.ls', [$this, 'requestCoreCapabilities']);
		$eventEmitter->on('irc.cap.ls.after', [$this, 'flushRequestQueue']);
		$eventEmitter->on('irc.cap.acknowledged', [$this, 'tryEndNegotiation']);
		$eventEmitter->on('irc.cap.notAcknowledged', [$this, 'tryEndNegotiation']);
		$eventEmitter->on('irc.sasl.complete', [$this, 'tryEndNegotiation']);
		$eventEmitter->on('irc.sasl.error', [$this, 'tryEndNegotiation']);
		$this->setContainer($container);

		$this->setSasl($SASL);
	}

	/**
	 * @param array $availableCapabilities
	 */
	public function requestCoreCapabilities(array $availableCapabilities)
	{
		if (in_array('extended-join', $availableCapabilities))
			$this->requestCapability('extended-join');

		if (in_array('account-notify', $availableCapabilities))
			$this->requestCapability('account-notify');

		if (in_array('multi-prefix', $availableCapabilities))
			$this->requestCapability('multi-prefix');
	}

	/**
	 * @param Queue $queue
	 */
	public function initNegotiation(Queue $queue)
	{
		Logger::fromContainer($this->getContainer())
			->debug('Capability negotiation started, requesting list of capabilities.');
		$queue->cap('LS');
	}

	/**
	 * @param Queue $queue
	 */
	public function flushRequestQueue(Queue $queue)
	{
		if (empty($this->getCapabilitiesToRequest()))
			return;

		Logger::fromContainer($this->getContainer())
			->debug('Sending capability request.', ['capabilitiesToRequest' => $this->getCapabilitiesToRequest()]);
		$queue->cap('REQ :' . implode(' ', $this->getCapabilitiesToRequest()));
	}

	/**
	 * @return bool
	 */
	public function tryEndNegotiation(): bool
	{
		if (!$this->canEndNegotiation())
			return false;

		Logger::fromContainer($this->getContainer())
			->debug('Ending capability negotiation.');
		Queue::fromContainer($this->getContainer())
			->cap('END');
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.cap.end');

		return true;
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function requestCapability(string $capability)
	{
		if (!$this->isCapabilityAvailable($capability))
		{
			Logger::fromContainer($this->getContainer())
				->warning('Capability was requested, but is not available on the server.',
					[
						'capability' => $capability,
						'availableCapabilities' => $this->getAvailableCapabilities()
					]);

			return false;
		}

		if ($this->isCapabilityAcknowledged($capability))
			return true;

		if (in_array($capability, $this->getCapabilitiesToRequest()))
			return true;

		Logger::fromContainer($this->getContainer())
			->debug('Capability queued for request on next flush.', ['capability' => $capability]);
		$this->capabilitiesToRequest[] = $capability;

		return true;
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function isCapabilityAvailable(string $capability): bool
	{
		return in_array($capability, $this->availableCapabilities);
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function isCapabilityAcknowledged(string $capability): bool
	{
		return in_array($capability, $this->acknowledgedCapabilities);
	}

	/**
	 * @param CAP $incomingIrcMessage
	 * @param Queue $queue
	 */
	public function responseRouter(CAP $incomingIrcMessage, Queue $queue)
	{
		$command = $incomingIrcMessage->getCommand();
		$capabilities = $incomingIrcMessage->getCapabilities();

		switch ($command)
		{
			case 'LS':
				$this->updateAvailableCapabilities($capabilities, $queue);
				EventEmitter::fromContainer($this->getContainer())
					->emit('irc.cap.ls.after', [$queue]);
				break;

			case 'ACK':
				$this->updateAcknowledgedCapabilities($capabilities, $queue);
				break;

			case 'NAK':
				$this->updateNotAcknowledgedCapabilities($capabilities, $queue);
				break;
		}
	}

	/**
	 * @param array $capabilities
	 * @param Queue $queue
	 */
	protected function updateAvailableCapabilities(array $capabilities, Queue $queue)
	{
		$this->setAvailableCapabilities($capabilities);
		Logger::fromContainer($this->getContainer())
			->debug('Updated list of available capabilities.',
				[
					'availableCapabilities' => $capabilities
				]);
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.cap.ls', [$capabilities, $queue]);
	}

	/**
	 * @param string[]|string $capabilities
	 * @param Queue $queue
	 */
	public function updateAcknowledgedCapabilities($capabilities, Queue $queue)
	{

		if (is_string($capabilities))
			$capabilities = [$capabilities];

		$ackCapabilities = array_filter(array_unique(array_merge($this->getAcknowledgedCapabilities(), $capabilities)));
		$this->setAcknowledgedCapabilities($ackCapabilities);
		Logger::fromContainer($this->getContainer())
			->debug('Updated list of acknowledged capabilities.',
				[
					'acknowledgedCapabilities' => $ackCapabilities
				]);
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.cap.acknowledged', [$ackCapabilities, $queue]);
	}

	/**
	 * @param string[]|string $capabilities
	 * @param Queue $queue
	 */
	public function updateNotAcknowledgedCapabilities($capabilities, Queue $queue)
	{
		if (is_string($capabilities))
			$capabilities = [$capabilities];

		$nakCapabilities = array_filter(array_unique(array_merge($this->getNotAcknowledgedCapabilities(), $capabilities)));
		$this->setNotAcknowledgedCapabilities($nakCapabilities);
		Logger::fromContainer($this->getContainer())
			->debug('Updated list of not acknowledged capabilities.',
				[
					'notAcknowledgedCapabilities' => $nakCapabilities
				]);
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.cap.notAcknowledged', [$nakCapabilities, $queue]);
	}

	/**
	 * @return bool
	 */
	public function canEndNegotiation(): bool
	{
		$saslIsComplete = $this->getSasl()
			->hasCompleted();

		$reqCount = count($this->getCapabilitiesToRequest());
		$ackCount = count($this->getAcknowledgedCapabilities());
		$nakCount = count($this->getNotAcknowledgedCapabilities());
		$handledCount = $ackCount + $nakCount;

		return $handledCount === $reqCount && $saslIsComplete;
	}

	/**
	 * @return array
	 */
	public function getAvailableCapabilities(): array
	{
		return $this->availableCapabilities;
	}

	/**
	 * @param array $availableCapabilities
	 */
	public function setAvailableCapabilities(array $availableCapabilities)
	{
		$this->availableCapabilities = $availableCapabilities;
	}

	/**
	 * @return array
	 */
	public function getCapabilitiesToRequest(): array
	{
		return $this->capabilitiesToRequest;
	}

	/**
	 * @param array $capabilitiesToRequest
	 */
	public function setCapabilitiesToRequest(array $capabilitiesToRequest)
	{
		$this->capabilitiesToRequest = $capabilitiesToRequest;
	}

	/**
	 * @return array
	 */
	public function getAcknowledgedCapabilities(): array
	{
		return $this->acknowledgedCapabilities;
	}

	/**
	 * @param array $acknowledgedCapabilities
	 */
	public function setAcknowledgedCapabilities(array $acknowledgedCapabilities)
	{
		$this->acknowledgedCapabilities = $acknowledgedCapabilities;
	}

	/**
	 * @return array
	 */
	public function getNotAcknowledgedCapabilities(): array
	{
		return $this->notAcknowledgedCapabilities;
	}

	/**
	 * @param array $notAcknowledgedCapabilities
	 */
	public function setNotAcknowledgedCapabilities(array $notAcknowledgedCapabilities)
	{
		$this->notAcknowledgedCapabilities = $notAcknowledgedCapabilities;
	}

	/**
	 * @return SASL
	 */
	public function getSasl(): SASL
	{
		return $this->sasl;
	}

	/**
	 * @param SASL $sasl
	 */
	public function setSasl(SASL $sasl)
	{
		$this->sasl = $sasl;
	}
}