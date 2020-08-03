<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcUser;

interface IrcUserStorageInterface
{
    /**
     * @param IrcUser $user
     */
    public function store(IrcUser $user): void;

    /**
     * @param IrcUser $user
     */
    public function delete(IrcUser $user): void;

    /**
     * @param int $userId
     * @return bool
     */
    public function has(int $userId): bool;

    /**
     * @param IrcUser $user
     * @return bool
     */
    public function contains(IrcUser $user): bool;

    /**
     * @param int $userId
     * @return null|IrcUser
     */
    public function getOne(int $userId): ?IrcUser;

    /**
     * @param string $nickname
     * @return null|IrcUser
     */
    public function getOneByNickname(string $nickname): ?IrcUser;

    /**
     * @param string $nickname
     * @return IrcUser
     */
    public function getOrCreateOneByNickname(string $nickname): IrcUser;

    /**
     * @param string $hostname
     * @return null|IrcUser
     */
    public function getOneByHostname(string $hostname): ?IrcUser;

    /**
     * @param string $username
     * @return null|IrcUser
     */
    public function getOneByUsername(string $username): ?IrcUser;

    /**
     * @param string $ircAccount
     * @return null|IrcUser
     */
    public function getOneByIrcAccount(string $ircAccount): ?IrcUser;

    /**
     * @return IrcUser[]
     */
    public function getAll(): array;
}
