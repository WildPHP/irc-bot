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
 * Class KICK
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix KICK #channel nickname :message
 */
class KICK extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	use ChannelTrait;
	use PrefixTrait;
	use NicknameTrait;
	use MessageTrait;

	protected static $verb = 'KICK';

	/**
	 * @var string
	 */
	protected $target = '';

	/**
	 * KICK constructor.
	 *
	 * @param string $channel
	 * @param string $nickname
	 * @param string $message
	 */
	public function __construct(string $channel, string $nickname, string $message)
	{
		$this->setChannel($channel);
		$this->setTarget($nickname);
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

		$args = $incomingIrcMessage->getArgs();
		$channel = $args[0];
		$target = $args[1];
		$message = $args[2];

		$object = new self($channel, $target, $message);
		$object->setPrefix($prefix);
		$object->setNickname($prefix->getNickname());

		return $object;
	}

	/**
	 * @return string
	 */
	public function getTarget(): string
	{
		return $this->target;
	}

	/**
	 * @param string $target
	 */
	public function setTarget(string $target)
	{
		$this->target = $target;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'KICK ' . $this->getChannel() . ' ' . $this->getTarget() . ' :' . $this->getMessage() . "\r\n";
	}
}