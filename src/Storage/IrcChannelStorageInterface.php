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

interface IrcChannelStorageInterface
{
    /**
     * @param IrcChannel $channel
     */
    public function store(IrcChannel $channel): void;

    /**
     * @param IrcChannel $channel
     */
    public function delete(IrcChannel $channel): void;

    /**
     * @param int $id
     * @return null|IrcChannel
     */
    public function getOne(int $id): ?IrcChannel;

    /**
     * @param string $name
     * @return null|IrcChannel
     */
    public function getOneByName(string $name): ?IrcChannel;

    /**
     * @param int $channelId
     * @return IrcUser[]
     */
    public function getRelatedUsers(int $channelId): array;

    /**
     * @param string $channelName
     * @return IrcUser[]
     */
    public function getRelatedUsersByChannelName(string $channelName): array;
}