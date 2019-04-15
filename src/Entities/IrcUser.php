<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

class IrcUser
{
    /**
     * @var int
     */
    private $userId;

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
    private $ircAccount;

    /**
     * IrcUser constructor.
     * @param string $nickname
     * @param int $userId
     * @param string $hostname
     * @param string $username
     * @param string $ircAccount
     */
    public function __construct(
        string $nickname,
        int $userId = 0,
        string $hostname = '',
        string $username = '',
        string $ircAccount = ''
    ) {
        $this->nickname = $nickname;
        $this->hostname = $hostname;
        $this->username = $username;
        $this->ircAccount = $ircAccount;
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
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
        return $this->ircAccount;
    }

    /**
     * @param string $ircAccount
     */
    public function setIrcAccount(string $ircAccount): void
    {
        $this->ircAccount = $ircAccount;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getUserId(),
            'nickname' => $this->getNickname(),
            'hostname' => $this->getHostname(),
            'username' => $this->getUsername(),
            'irc_account' => $this->getIrcAccount()
        ];
    }

    /**
     * @param string[] $previousState
     * @return IrcUser
     */
    public static function fromArray(array $previousState): IrcUser
    {
        $nickname = $previousState['nickname'] ?? '';
        $userId = (int)($previousState['id'] ?? 0);
        $hostname = $previousState['hostname'] ?? '';
        $username = $previousState['username'] ?? '';
        $ircAccount = $previousState['irc_account'] ?? '';
        return new IrcUser($nickname, $userId, $hostname, $username, $ircAccount);
    }
}
