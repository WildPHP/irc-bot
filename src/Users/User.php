<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;

use WildPHP\Core\Database\Database;
use WildPHP\Core\StateException;

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
     * @var int
     */
	protected $id = 0;

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
     * @param Database $db
     * @param array $where
     * @return User
     * @throws UserNotFoundException
     * @throws StateException
     */
    public static function fromDatabase(Database $db, array $where)
    {
        if (!$db->has('users', [], $where))
            throw new UserNotFoundException();

        $data = $db->get('users', ['id', 'nickname', 'hostname', 'username', 'irc_account'], $where);

        if (!$data)
            throw new StateException('Tried to get 1 channel from database but received none or multiple... State mismatch!');

        $user = new User($data['nickname'], $data['hostname'], $data['username'], $data['irc_account']);
        $user->setId($data['id']);
        return $user;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}