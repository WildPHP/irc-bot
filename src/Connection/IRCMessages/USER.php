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
 * Class USER
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix USER username hostname servername realname
 */
class USER implements BaseMessage, SendableMessage
{
	protected static $verb = 'USER';

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var string
	 */
	protected $hostname = '';

	/**
	 * @var string
	 */
	protected $servername = '';

	/**
	 * @var string
	 */
	protected $realname = '';

	public function __construct(string $username, string $hostname, string $servername, string $realname)
	{
		$this->setUsername($username);
		$this->setHostname($hostname);
		$this->setServername($servername);
		$this->setRealname($realname);
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

		$args = $incomingIrcMessage->getArgs();
		$username = array_shift($args);
		$hostname = array_shift($args);
		$servername = array_shift($args);
		$realname = array_shift($args);

		$object = new self($username, $hostname, $servername, $realname);

		return $object;
	}

	public function __toString()
	{
		$username = $this->getUsername();
		$hostname = $this->getHostname();
		$servername = $this->getServername();
		$realname = $this->getRealname();

		return 'USER ' . $username . ' ' . $hostname . ' ' . $servername . ' ' . $realname . "\r\n";
	}

	/**
	 * @return string
	 */
	public function getUsername(): string
	{
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername(string $username)
	{
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getHostname(): string
	{
		return $this->hostname;
	}

	/**
	 * @param string $hostname
	 */
	public function setHostname(string $hostname)
	{
		$this->hostname = $hostname;
	}

	/**
	 * @return string
	 */
	public function getServername(): string
	{
		return $this->servername;
	}

	/**
	 * @param string $servername
	 */
	public function setServername(string $servername)
	{
		$this->servername = $servername;
	}

	/**
	 * @return string
	 */
	public function getRealname(): string
	{
		return $this->realname;
	}

	/**
	 * @param string $realname
	 */
	public function setRealname(string $realname)
	{
		$this->realname = $realname;
	}
}