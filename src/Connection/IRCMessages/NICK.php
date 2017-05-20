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
 * Class NICK
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix NICK newnickname
 */
class NICK implements ReceivableMessage, SendableMessage
{
	use PrefixTrait;
	use NicknameTrait;

	protected static $verb = 'NICK';

	/**
	 * @var string
	 */
	protected $newNickname = '';

	public function __construct(string $newNickname)
	{
		$this->setNewNickname($newNickname);
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
		$nickname = $prefix->getNickname();
		$newNickname = $incomingIrcMessage->getArgs()[0];

		$object = new self($newNickname);
		$object->setPrefix($prefix);
		$object->setNickname($nickname);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getNewNickname(): string
	{
		return $this->newNickname;
	}

	/**
	 * @param string $newNickname
	 */
	public function setNewNickname(string $newNickname)
	{
		$this->newNickname = $newNickname;
	}

	public function __toString()
	{
		return 'NICK ' . $this->getNewNickname() . "\r\n";
	}
}