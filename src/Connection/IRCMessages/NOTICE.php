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

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class NOTICE
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix NOTICE #channel :message
 */
class NOTICE implements BaseMessage, SendableMessage
{
	use PrefixTrait;
	use ChannelTrait;
	use NicknameTrait;
	use MessageTrait;

	/**
	 * @var string
	 */
	protected static $verb = 'NOTICE';

	public function __construct(string $channel, string $message)
	{
		$this->setChannel($channel);
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
		$channel = $incomingIrcMessage->getArgs()[0];
		$message = $incomingIrcMessage->getArgs()[1];

		$object = new self($channel, $message);
		$object->setPrefix($prefix);
		$object->setNickname($prefix->getNickname());

		return $object;
	}

	public function __toString()
	{
		return 'NOTICE ' . $this->getChannel() . ' :' . $this->getMessage() . "\r\n";
	}
}