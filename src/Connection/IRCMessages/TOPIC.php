<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class TOPIC
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix TOPIC channel :topic
 */
class TOPIC implements ReceivableMessage, SendableMessage
{
	protected static $verb = 'TOPIC';

	use MessageTrait;
	use ChannelTrait;
	use PrefixTrait;

	/**
	 * @param string $channelName
	 * @param string $message
	 */
	public function __construct(string $channelName, string $message)
	{
		$this->setChannel($channelName);
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
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$args = $incomingIrcMessage->getArgs();
		$channel = array_shift($args);
		$message = array_shift($args);

		$object = new self($channel, $message);
		$object->setPrefix($prefix);

		return $object;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return 'TOPIC ' . $this->getChannel() . ' :' . $this->getMessage() . "\r\n";
	}
}