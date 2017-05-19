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
 * Class RPL_WHOSPCRPL
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax (as used by WildPHP): :server 354 ownnickname username hostname nickname status accountname
 */
class RPL_WHOSPCRPL
{
	use NicknameTrait;
	use ChannelTrait;
	use MessageTrait;

	protected static $verb = '354';

	/**
	 * @var string
	 */
	protected $ownNickname = '';

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
	protected $status = '';

	/**
	 * @var string
	 */
	protected $accountname = '';

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
		$ownNickname = array_shift($args);
		$username = array_shift($args);
		$hostname = array_shift($args);
		$nickname = array_shift($args);
		$status = array_shift($args);
		$accountname = array_shift($args);

		$object = new self();
		$object->setOwnNickname($ownNickname);
		$object->setUsername($username);
		$object->setHostname($hostname);
		$object->setNickname($nickname);
		$object->setStatus($status);
		$object->setAccountname($accountname);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getOwnNickname(): string
	{
		return $this->ownNickname;
	}

	/**
	 * @param string $ownNickname
	 */
	public function setOwnNickname(string $ownNickname)
	{
		$this->ownNickname = $ownNickname;
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
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus(string $status)
	{
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getAccountname(): string
	{
		return $this->accountname;
	}

	/**
	 * @param string $accountname
	 */
	public function setAccountname(string $accountname)
	{
		$this->accountname = $accountname;
	}
}