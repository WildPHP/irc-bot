<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


class ConnectionDetails
{
	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $hostname;

	/**
	 * @var string
	 */
	protected $address;

	/**
	 * @var int
	 */
	protected $port;

	/**
	 * @var string
	 */
	protected $realname;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $wantedNickname;

	/**
	 * @var boolean
	 */
	protected $secure = false;

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
	public function getAddress(): string
	{
		return $this->address;
	}

	/**
	 * @param string $address
	 */
	public function setAddress(string $address)
	{
		$this->address = $address;
	}

	/**
	 * @return int
	 */
	public function getPort(): int
	{
		return $this->port;
	}

	/**
	 * @param int $port
	 */
	public function setPort(int $port)
	{
		$this->port = $port;
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

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword(string $password)
	{
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getWantedNickname(): string
	{
		return $this->wantedNickname;
	}

	/**
	 * @param string $wantedNickname
	 */
	public function setWantedNickname(string $wantedNickname)
	{
		$this->wantedNickname = $wantedNickname;
	}

	/**
	 * @return bool
	 */
	public function getSecure(): bool
	{
		return $this->secure;
	}

	/**
	 * @param bool $secure
	 */
	public function setSecure(bool $secure)
	{
		$this->secure = $secure;
	}
}