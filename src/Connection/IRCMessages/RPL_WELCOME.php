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
 * Class RPL_WELCOME
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: :server 001 nickname :greeting
 */
class RPL_WELCOME
{
	use NicknameTrait;

	protected static $verb = '001';

	protected $server = '';

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

		$nickname = $incomingIrcMessage->getArgs()[0];
		$server = $incomingIrcMessage->getPrefix();
		$object = new self();
		$object->setNickname($nickname);
		$object->setServer($server);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getServer(): string
	{
		return $this->server;
	}

	/**
	 * @param string $server
	 */
	public function setServer(string $server)
	{
		$this->server = $server;
	}
}