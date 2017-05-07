<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\ConfigurationItemNotFoundException;
use WildPHP\Core\Connection\Commands\Authenticate;
use WildPHP\Core\Connection\IRCMessages\AUTHENTICATE as IncomingAuthenticate;
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
		try
		{
			Configuration::fromContainer($container)
				->get('sasl');
			Configuration::fromContainer($container)
				->get('sasl.username');
			Configuration::fromContainer($container)
				->get('sasl.password');

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
		catch (ConfigurationItemNotFoundException $e)
		{
			Logger::fromContainer($container)
				->info('SASL not initialized because no credentials were provided.');
			$this->setHasCompleted(true);
		}
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

		$queue->insertMessage(new Authenticate('PLAIN'));
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
	 * @param IncomingAuthenticate $message
	 * @param Queue $queue
	 */
	public function sendCredentials(IncomingAuthenticate $message, Queue $queue)
	{
		if ($message->getResponse() != '+')
			return;

		$username = Configuration::fromContainer($this->getContainer())
			->get('sasl.username')
			->getValue();
		$password = Configuration::fromContainer($this->getContainer())
			->get('sasl.password')
			->getValue();
		$credentials = $this->generateCredentialString($username, $password);
		$queue->insertMessage(new Authenticate($credentials));
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