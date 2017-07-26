<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\AUTHENTICATE;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;

class SASL extends BaseModule implements EventEmitterInterface
{
	use ContainerTrait;
	use EventEmitterTrait;

	/**
	 * @var array
	 */
	protected $saslCodes = [
		'903' => 'RPL_SASLSUCCESS',
		'908' => 'RPL_SASLMECHS',

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
			$this->setContainer($container);
			Logger::fromContainer($container)
				->info('[SASL] Not initialized because no credentials were provided.');
			$this->completeSasl();

			return;
		}

		CapabilityHandler::fromContainer($container)
			->requestCapability('sasl');

		EventEmitter::fromContainer($container)
			->on('irc.cap.acknowledged.sasl', [$this, 'sendAuthenticationMechanism']);
		EventEmitter::fromContainer($container)
			->on('irc.cap.notAcknowledged.sasl', [$this, 'completeSasl']);
		EventEmitter::fromContainer($container)
			->on('irc.line.in.authenticate', [$this, 'sendCredentials']);

		// Map all numeric SASL responses to either the success or error handler:
		EventEmitter::fromContainer($container)
			->on('irc.line.in', [$this, 'handleResponse']);

		Logger::fromContainer($container)
			->debug('[SASL] Initialized, awaiting server response.');

		$this->setContainer($container);
	}

	public function completeSasl()
	{
		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.sasl.complete');
	}

	/**
	 * @param Queue $queue
	 */
	public function sendAuthenticationMechanism(Queue $queue)
	{
		$queue->authenticate('PLAIN');
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
		$queue->authenticate($credentials);

		Logger::fromContainer($this->getContainer())
			->debug('[SASL] Sent authentication details, awaiting response from server.');
	}

	/**
	 * @param IncomingIrcMessage $message
	 */
	public function handleResponse(IncomingIrcMessage $message)
	{
		$code = $message->getVerb();

		if (!array_key_exists($code, $this->saslCodes))
			return;

		// This event has to fit on the events used in CapabilityHandler.
		Logger::fromContainer($this->getContainer())
			->info('[SASL] Authentication ended with code ' . $code . ' (' . $this->saslCodes[$code] . ')');

		$this->completeSasl();
	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}