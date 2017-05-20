<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
class PART implements ReceivableMessage, SendableMessage
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
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

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