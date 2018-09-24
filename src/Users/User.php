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
    public function __construct(
        string $nickname,
        ?string $hostname = '',
        ?string $username = '',
        ?string $ircAccount = ''
    ) {
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
        if (!$db->has('users', $where)) {
            throw new UserNotFoundException();
        }

        $data = $db->get('users', ['id', 'nickname', 'hostname', 'username', 'irc_account'], $where);

        if (!$data) {
            throw new StateException('Tried to get 1 user from database but received none or multiple... State mismatch!');
        }

        $user = new User($data['nickname'], $data['hostname'], $data['username'], $data['irc_account']);
        $user->setId($data['id']);
        return $user;

    }

    /**
     * @param Database $db
     * @param User $user
     * @return int The user id
     */
    public static function toDatabase(Database $db, User $user): int
    {
        $data = $user->toArray();

        if (empty($user->getId()) || !$db->has('users', [], ['id' => $user->getId()])) {
            // unset the id here so we don't overwrite or cause a potential error
            unset($data['id']);
            $db->insert('users', [$data]);

            return $db->id();
        }

        $db->update('users', $data, ['id' => $user->getId()]);
        return $user->getId();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'nickname' => $this->getNickname(),
            'username' => $this->getUsername(),
            'hostname' => $this->getHostname(),
            'irc_account' => $this->getIrcAccount()
        ];
    }

    /**
     * @return string
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname(?string $hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(?string $username)
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
    public function getIrcAccount(): ?string
    {
        return $this->ircAccount;
    }

    /**
     * @param string $ircAccount
     */
    public function setIrcAccount(?string $ircAccount)
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