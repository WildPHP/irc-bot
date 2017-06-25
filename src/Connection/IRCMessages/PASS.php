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
 * Class PASS
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: PASS password
 */
class PASS extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	protected static $verb = 'PASS';

	protected $password = '';

	public function __construct(string $password)
	{
		$this->setPassword($password);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return \self
	 * @throws \InvalidArgumentException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::getVerb())
			throw new \InvalidArgumentException('Expected incoming ' . self::getVerb() . '; got ' . $incomingIrcMessage->getVerb());

		$password = $incomingIrcMessage->getArgs()[0];

		$object = new self($password);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword(string $password)
	{
		$this->password = $password;
	}

	public function __toString()
	{
		return 'PASS :' . $this->getPassword() . "\r\n";
	}
}