<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcChannel;
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
     * @param int $id
     * @return null|IrcUser
     */
    public function getOne(int $id): ?IrcUser;

    /**
     * @param string $nickname
     * @return null|IrcUser
     */
    public function getOneByNickname(string $nickname): ?IrcUser;

    /**
     * @param string $property
     * @param $value
     * @return null|IrcUser
     */
    public function getOneByProperty(string $property, $value): ?IrcUser;

    /**
     * @param int $userId
     * @return IrcChannel[]
     */
    public function getRelatedChannels(int $userId): array;

    /**
     * @param string $nickname
     * @return IrcChannel[]
     */
    public function getRelatedChannelsByNickname(string $nickname): array;
}