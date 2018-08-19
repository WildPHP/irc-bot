<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class QUIT
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix QUIT :message
 */
class QUIT extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	use PrefixTrait;
	use NicknameTrait;
	use MessageTrait;

	protected static $verb = 'QUIT';

	/**
	 * QUIT constructor.
	 *
	 * @param string $message
	 */
	public function __construct(string $message)
	{
		$this->setMessage($message);
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

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$nickname = $prefix->getNickname();
		$message = $incomingIrcMessage->getArgs()[0];

		$object = new self($message);
		$object->setPrefix($prefix);
		$object->setNickname($nickname);

		return $object;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'QUIT :' . $this->getMessage() . "\r\n";
	}
}