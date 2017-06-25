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
 * Class PART
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix PART #channel [:message]
 * Syntax (sender): PART #channels [:message]
 */
class PART extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	/**
	 * @var string
	 */
	protected static $verb = 'PART';

	use ChannelsTrait;
	use NicknameTrait;
	use PrefixTrait;
	use MessageTrait;

	public function __construct($channels, $message = '')
	{
		if (!is_array($channels))
			$channels = [$channels];

		$this->setChannels($channels);
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
		$message = $args[1] ?? '';

		$object = new self($channel, $message);
		$object->setPrefix($prefix);
		$object->setNickname($prefix->getNickname());

		return $object;
	}

	public function __toString()
	{
		$channels = implode(',', $this->getChannels());
		$message = $this->getMessage();

		return 'PART ' . $channels . (!empty($message) ? ' :' . $message : '') . "\r\n";
	}
}