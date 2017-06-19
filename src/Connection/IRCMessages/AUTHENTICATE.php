<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Class AUTHENTICATE
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: AUTHENTICATE response
 * @TODO look into the documentation
 */
class AUTHENTICATE implements ReceivableMessage, SendableMessage
{
	protected static $verb = 'AUTHENTICATE';

	/**
	 * @var string
	 */
	protected $response = '';

	public function __construct(string $response)
	{
		$this->setResponse($response);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return \self
	 * @throws \InvalidArgumentException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());
		$response = $incomingIrcMessage->getArgs()[0];

		$object = new self($response);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getResponse(): string
	{
		return $this->response;
	}

	/**
	 * @param string $response
	 */
	public function setResponse(string $response)
	{
		$this->response = $response;
	}

	public function __toString()
	{
		return 'AUTHENTICATE ' . $this->getResponse() . "\r\n";
	}
}