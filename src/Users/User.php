<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;

class User
{
	/**
	 * @var string
	 */
	protected $nickname = '';

	/**
	 * @var string
	 */
	protected $hostname = '';

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var string
	 */
	protected $ircAccount = '';

	/**
	 * User constructor.
	 *
	 * @param string $nickname
	 * @param string $hostname
	 * @param string $username
	 * @param string $ircAccount
	 */
	public function __construct(string $nickname, string $hostname = '', string $username = '', string $ircAccount = '')
	{
		$this->setNickname($nickname);
		$this->setHostname($hostname);
		$this->setUsername($username);
		$this->setIrcAccount($ircAccount);
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
	public function getIrcAccount(): string
	{
		return $this->ircAccount;
	}

	/**
	 * @param string $ircAccount
	 */
	public function setIrcAccount(string $ircAccount)
	{
		$this->ircAccount = $ircAccount;
	}
}