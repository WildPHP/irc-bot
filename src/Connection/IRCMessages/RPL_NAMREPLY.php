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

/**
 * Class RPL_NAMREPLY
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: :server 353 nickname visibility channel :nicknames
 */
class RPL_NAMREPLY implements BaseMessage
{
	use NicknameTrait;
	use ChannelTrait;

	protected static $verb = '353';

	protected $nicknames = [];

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

		$args = $incomingIrcMessage->getArgs();
		$nickname = array_shift($args);
		$visibility = array_shift($args);
		$channel = array_shift($args);
		$nicknames = explode(' ', array_shift($args));

		$object = new self();
		$object->setNickname($nickname);
		$object->setChannel($channel);
		$object->setNicknames($nicknames);

		return $object;
	}

	/**
	 * @return array
	 */
	public function getNicknames(): array
	{
		return $this->nicknames;
	}

	/**
	 * @param array $nicknames
	 */
	public function setNicknames(array $nicknames)
	{
		$this->nicknames = $nicknames;
	}
}