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
use WildPHP\Core\Storage\Providers\DatabaseStorageProviderInterface;

class IrcChannelDatabaseStorage implements IrcChannelStorageInterface
{
    public static const TABLE = 'channels';

    /**
     * @var DatabaseStorageProviderInterface
     */
    private $databaseStorageProvider;

    /**
     * IrcChannelDatabaseStorage constructor.
     * @param DatabaseStorageProviderInterface $databaseStorageProvider
     */
    public function __construct(DatabaseStorageProviderInterface $databaseStorageProvider)
    {
        $this->databaseStorageProvider = $databaseStorageProvider;
    }

    /**
     * @param IrcChannel $channel
     */
    public function store(IrcChannel $channel): void
    {
        // TODO: Implement store() method.
    }

    /**
     * @param IrcChannel $channel
     */
    public function delete(IrcChannel $channel): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param int $id
     * @return null|IrcChannel
     */
    public function getOne(int $id): ?IrcChannel
    {
        // TODO: Implement getOne() method.
    }

    /**
     * @param string $name
     * @return null|IrcChannel
     */
    public function getOneByName(string $name): ?IrcChannel
    {
        // TODO: Implement getOneByName() method.
    }

    /**
     * @param int $channelId
     * @return IrcUser[]
     */
    public function getRelatedUsers(int $channelId): array
    {
        // TODO: Implement getRelatedUsers() method.
    }

    /**
     * @param string $channelName
     * @return IrcUser[]
     */
    public function getRelatedUsersByChannelName(string $channelName): array
    {
        // TODO: Implement getRelatedUsersByChannelName() method.
    }
}