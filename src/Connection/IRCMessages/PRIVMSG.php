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
 * Class PRIVMSG
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix PRIVMSG #channel :message
 */
class PRIVMSG implements ReceivableMessage, SendableMessage
{
	use PrefixTrait;
	use ChannelTrait;
	use NicknameTrait;
	use MessageTrait;

	protected static $verb = 'PRIVMSG';

	/**
	 * @var bool|string
	 */
	protected $ctcpVerb = false;

	/**
	 * @var bool
	 */
	protected $isCtcp = false;

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

		$isCtcp = substr($message, 0, 1) == "\x01" && substr($message, -1, 1) == "\x01";
		$ctcpVerb = false;

		if ($isCtcp)
		{
			$message = trim(substr($message, 1, -1));
			$message = explode(' ', $message, 2);
			$ctcpVerb = array_shift($message);
			$message = !empty($message) ? array_shift($message) : '';
			var_dump($ctcpVerb, $message);
		}

		$object = new self($channel, $message);
		$object->setPrefix($prefix);
		$object->setIsCtcp($isCtcp);
		$object->setCtcpVerb($ctcpVerb);
		$object->setNickname($prefix->getNickname());

		return $object;
	}

	/**
	 * @return bool|string
	 */
	public function getCtcpVerb()
	{
		return $this->ctcpVerb;
	}

	/**
	 * @param bool|string $ctcpVerb
	 */
	public function setCtcpVerb($ctcpVerb)
	{
		$this->ctcpVerb = $ctcpVerb;
	}

	/**
	 * @return bool
	 */
	public function isCtcp(): bool
	{
		return $this->isCtcp;
	}

	/**
	 * @param bool $isCtcp
	 */
	public function setIsCtcp(bool $isCtcp)
	{
		$this->isCtcp = $isCtcp;
	}

	public function __toString()
	{
		if ($this->isCtcp())
			$message = "\x01" . $this->getCtcpVerb() . ' ' . $this->getMessage() . "\x01";
		else
			$message = $this->getMessage();

		return 'PRIVMSG ' . $this->getChannel() . ' :' . $message . "\r\n";
	}
}