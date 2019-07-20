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
     * @var EntityModes
     */
    private $modes;

    /**
     * @var boolean
     */
    private $online;

    /**
     * IrcUser constructor.
     * @param string $nickname
     * @param int $userId
     * @param string $hostname
     * @param string $username
     * @param string $ircAccount
     * @param EntityModes|null $modes
     * @param bool $online
     */
    public function __construct(
        string $nickname,
        int $userId = 0,
        string $hostname = '',
        string $username = '',
        string $ircAccount = '',
        EntityModes $modes = null,
        bool $online = false
    ) {
        $this->nickname = $nickname;
        $this->hostname = $hostname;
        $this->username = $username;
        $this->ircAccount = $ircAccount;
        $this->userId = $userId;
        $this->modes = $modes ?? new EntityModes();
        $this->online = $online;
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
     * @return EntityModes
     */
    public function getModes(): EntityModes
    {
        return $this->modes;
    }

    /**
     * @param EntityModes $modes
     */
    public function setModes(EntityModes $modes): void
    {
        $this->modes = $modes;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     */
    public function setOnline(bool $online): void
    {
        $this->online = $online;
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
            'irc_account' => $this->getIrcAccount(),
            'modes' => $this->getModes()->toArray(),
            'online' => $this->isOnline() ? 1 : 0
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
        $modes = new EntityModes((array)($previousState['modes'] ?? []));
        $online = $previousState['online'] === 1;
        return new IrcUser($nickname, $userId, $hostname, $username, $ircAccount, $modes, $online);
    }
}
