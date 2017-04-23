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

namespace WildPHP\Core\Connection;


class UserPrefix
{
	/**
	 * @var string
	 */
	static $regex = "/^(?<nick>[^!]+) (?:!(?<user>[^@]+))? (?:@(?<host>.+))?$/x";

	/**
	 * @var string
	 */
	protected $nickname = '';

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var string
	 */
	protected $hostname = '';

	public function __construct(string $nickname = '', string $username = '', string $hostname = '')
	{
		$this->setNickname($nickname);
		$this->setUsername($username);
		$this->setHostname($hostname);
	}

	/**
	 * @return string
	 */
	public function getNickname(): string
	{
		return $this->nickname;
	}

	/**
	 * @param string $nickname
	 */
	public function setNickname(string $nickname)
	{
		$this->nickname = $nickname;
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
	 * @param string $prefix
	 *
	 * @return UserPrefix
	 */
	public static function fromString(string $prefix): self
	{
		if (preg_match(self::$regex, $prefix, $matches) == false)
			throw new \InvalidArgumentException('Got invalid prefix');

		$nickname = $matches['nick'];
		$username = $matches['user'] ?? '';
		$hostname = $matches['host'] ?? '';

		return new self($nickname, $username, $hostname);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return UserPrefix
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		return self::fromString($incomingIrcMessage->getPrefix());
	}
}