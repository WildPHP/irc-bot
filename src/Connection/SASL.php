<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\AUTHENTICATE;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;

class SASL
{
	use ContainerTrait;

	/**
	 * @var bool
	 */
	protected $hasCompleted = false;
	/**
	 * @var bool|string
	 */
	protected $errorReason = false;
	/**
	 * @var bool
	 */
	protected $isSuccessful = false;

	/**
	 * @var array
	 */
	protected $successCodes = [
		'900' => 'RPL_LOGGEDIN',
		'901' => 'RPL_LOGGEDOUT',
		'903' => 'RPL_SASLSUCCESS',
		'908' => 'RPL_SASLMECHS'
	];

	/**
	 * @var array
	 */
	protected $errorCodes = [
		'902' => 'ERR_NICKLOCKED',
		'904' => 'ERR_SASLFAIL',
		'905' => 'ERR_SASLTOOLONG',
		'906' => 'ERR_SASLABORTED',
		'907' => 'ERR_SASLALREADY'
	];

	/**
	 * SASL constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		if (!Configuration::fromContainer($container)->offsetExists('sasl') ||
			empty(Configuration::fromContainer($container)['sasl']['username']) ||
			empty(Configuration::fromContainer($container)['sasl']['password'])
		)
		{
			Logger::fromContainer($container)
				->info('[SASL] Not initialized because no credentials were provided.');
			$this->setHasCompleted(true);

			return;
		}

		EventEmitter::fromContainer($container)
			->on('irc.cap.acknowledged', [$this, 'sendAuthenticationMechanism']);
		EventEmitter::fromContainer($container)
			->on('irc.line.in.authenticate', [$this, 'sendCredentials']);
		EventEmitter::fromContainer($container)
			->on('irc.cap.ls', [$this, 'requestCapability']);

		// Map all numeric SASL responses to either the success or error handler:
		EventEmitter::fromContainer($container)
			->on('irc.line.in', [$this, 'handlePositiveResponse']);
		EventEmitter::fromContainer($container)
			->on('irc.line.in', [$this, 'handleNegativeResponse']);

		Logger::fromContainer($container)
			->debug('[SASL] Initialized, awaiting server response.');
		$this->setContainer($container);
	}

	public function requestCapability()
	{
		CapabilityHandler::fromContainer($this->getContainer())
			->requestCapability('sasl');
	}

	/**
	 * @param array $acknowledgedCapabilities
	 * @param Queue $queue
	 */
	public function sendAuthenticationMechanism(array $acknowledgedCapabilities, Queue $queue)
	{
		if (!in_array('sasl', $acknowledgedCapabilities))
			return;

		$queue->insertMessage(new AUTHENTICATE('PLAIN'));
		Logger::fromContainer($this->getContainer())
			->debug('[SASL] Authentication mechanism requested, awaiting server response.');
	}

	/**
	 * @param string $username
	 * @param string $password
	 *
	 * @return string
	 */
	protected function generateCredentialString(string $username, string $password)
	{
		return base64_encode($username . "\0" . $username . "\0" . $password);
	}

	/**
	 * @param AUTHENTICATE $message
	 * @param Queue $queue
	 */
	public function sendCredentials(AUTHENTICATE $message, Queue $queue)
	{
		if ($message->getResponse() != '+')
			return;

		$username = Configuration::fromContainer($this->getContainer())['sasl']['username'];
		$password = Configuration::fromContainer($this->getContainer())['sasl']['password'];
		$credentials = $this->generateCredentialString($username, $password);
		$queue->insertMessage(new AUTHENTICATE($credentials));
		Logger::fromContainer($this->getContainer())
			->debug('[SASL] Sent authentication details, awaiting response from server.');
	}

	/**
	 * @param IncomingIrcMessage $message
	 * @param Queue $queue
	 */
	public function handlePositiveResponse(IncomingIrcMessage $message, Queue $queue)
	{
		$code = $message->getVerb();
		if (!array_key_exists($code, $this->successCodes))
			return;

		$this->setErrorReason(false);
		$this->setHasCompleted(true);
		$this->setIsSuccessful(true);

		if ($code != '903')
			return;

		// This event has to fit on the events used in CapabilityHandler.
		Logger::fromContainer($this->getContainer())
			->info('[SASL] Authentication successful!');
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.sasl.complete', [[], $queue]);
	}

	/**
	 * @param IncomingIrcMessage $message
	 */
	public function handleNegativeResponse(IncomingIrcMessage $message)
	{
		$code = $message->getVerb();
		if (!array_key_exists($code, $this->errorCodes))
			return;

		$reason = $this->errorCodes[$code];

		$this->setErrorReason($reason);
		$this->setHasCompleted(true);
		$this->setIsSuccessful(false);

		// This event has to fit on the events used in CapabilityHandler.
		Logger::fromContainer($this->getContainer())
			->warning('[SASL] Authentication was NOT successful. Continuing unauthenticated.');
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.sasl.error');
	}

	/**
	 * @param string|false $reason
	 */
	public function setErrorReason($reason)
	{
		$this->errorReason = $reason;
	}

	/**
	 * @param boolean $hasCompleted
	 */
	public function setHasCompleted(bool $hasCompleted)
	{
		$this->hasCompleted = $hasCompleted;
	}

	/**
	 * @param boolean $isSuccessful
	 */
	public function setIsSuccessful(bool $isSuccessful)
	{
		$this->isSuccessful = $isSuccessful;
	}

	/**
	 * @return bool
	 */
	public function hasCompleted(): bool
	{
		return $this->hasCompleted;
	}

	/**
	 * @return bool
	 */
	public function isSuccessful(): bool
	{
		return $this->isSuccessful;
	}

	/**
	 * @return bool|string
	 */
	public function hasEncounteredError()
	{
		return $this->errorReason;
	}
}