<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Entities;

class IrcUser
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $nickname;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $irc_account;

    /**
     * IrcUser constructor.
     * @param string $nickname
     * @param int $id
     * @param string $hostname
     * @param string $username
     * @param string $irc_account
     */
    public function __construct(
        string $nickname,
        $id = 0,
        string $hostname = '',
        string $username = '',
        string $irc_account = ''
    ) {
        $this->nickname = $nickname;
        $this->hostname = $hostname;
        $this->username = $username;
        $this->irc_account = $irc_account;
        $this->id = $id;
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
    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
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
    public function setHostname(string $hostname): void
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
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getIrcAccount(): string
    {
        return $this->irc_account;
    }

    /**
     * @param string $irc_account
     */
    public function setIrcAccount(string $irc_account): void
    {
        $this->irc_account = $irc_account;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'nickname' => $this->getNickname(),
            'hostname' => $this->getHostname(),
            'username' => $this->getUsername(),
            'irc_account' => $this->getIrcAccount()
        ];
    }

    /**
     * @param array $previousState
     * @return IrcUser
     */
    public static function fromArray(array $previousState): IrcUser
    {
        $nickname = $previousState['nickname'] ?? '';
        $id = $previousState['id'] ?? '';
        $hostname = $previousState['hostname'] ?? '';
        $username = $previousState['username'] ?? '';
        $irc_account = $previousState['irc_account'] ?? '';
        return new IrcUser($nickname, $id, $hostname, $username, $irc_account);
    }
}