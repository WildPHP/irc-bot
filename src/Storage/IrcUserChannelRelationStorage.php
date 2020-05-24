<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcUserChannelRelation;
use WildPHP\Core\Storage\Providers\StorageProviderInterface;

class IrcUserChannelRelationStorage implements IrcUserChannelRelationStorageInterface
{
    /**
     * @var StorageProviderInterface
     */
    private $storageProvider;

    /**
     * @var string
     */
    private $database;

    public function __construct(StorageProviderInterface $storageProvider, string $database = 'user_channels')
    {
        $this->storageProvider = $storageProvider;
        $this->database = $database;
        $storageProvider->deleteAllWithCriteria($database, []);
    }

    /**
     * @param int $userId
     * @param int $channelId
     * @return bool
     */
    public function has(int $userId, int $channelId): bool
    {
        return $this->storageProvider->has($this->database, ['id_user' => $userId, 'id_channel' => $channelId]);
    }

    /**
     * @param IrcUserChannelRelation $relation
     */
    public function store(IrcUserChannelRelation $relation): void
    {
        $this->storageProvider->store(
            $this->database,
            IrcUserChannelRelationStorageAdapter::convertToStoredEntity($relation)
        );
    }

    /**
     * @param IrcUserChannelRelation $relation
     */
    public function delete(IrcUserChannelRelation $relation): void
    {
        $this->storageProvider->delete($this->database, $relation->toArray());
    }

    /**
     * @param IrcUserChannelRelation $relation
     * @return bool
     */
    public function contains(IrcUserChannelRelation $relation): bool
    {
        return $this->storageProvider->has($this->database, $relation->toArray());
    }

    /**
     * @param int $userId
     * @param int $channelId
     * @return null|IrcUserChannelRelation
     */
    public function getOne(int $userId, int $channelId): ?IrcUserChannelRelation
    {
        $entity = $this->storageProvider->retrieve($this->database, ['user_id' => $userId, 'channel_id' => $channelId]);

        if ($entity === null) {
            return null;
        }

        return IrcUserChannelRelationStorageAdapter::convertToIrcUserChannelRelation($entity);
    }

    /**
     * @param int $userId
     * @param int $channelId
     * @return IrcUserChannelRelation
     */
    public function getOrCreateOne(int $userId, int $channelId): IrcUserChannelRelation
    {
        $entity = $this->getOne($userId, $channelId);

        if ($entity !== null) {
            return $entity;
        }

        $relation = new IrcUserChannelRelation(
            [
                'ircUserId' => $userId,
                'ircChannelId' => $channelId
            ]
        );
        $this->store($relation);
        return $relation;
    }

    /**
     * @param int $userId
     * @return null|IrcUserChannelRelation[]
     */
    public function getByUserId(int $userId): ?array
    {
        $entries = $this->storageProvider->retrieveAll($this->database, ['user_id' => $userId]);

        if ($entries === null) {
            return null;
        }

        foreach ($entries as $key => $entry) {
            $entries[$key] = IrcUserChannelRelationStorageAdapter::convertToIrcUserChannelRelation($entry);
        }

        return $entries;
    }

    /**
     * @param int $channelId
     * @return null|IrcUserChannelRelation[]
     */
    public function getByChannelId(int $channelId): ?array
    {
        $entries = $this->storageProvider->retrieveAll($this->database, ['channel_id' => $channelId]);

        if ($entries === null) {
            return null;
        }

        foreach ($entries as $key => $entry) {
            $entries[$key] = IrcUserChannelRelationStorageAdapter::convertToIrcUserChannelRelation($entry);
        }

        return $entries;
    }

    /**
     * @return IrcUserChannelRelation[]
     */
    public function getAll(): array
    {
        $entries = $this->storageProvider->retrieveAll($this->database);

        if ($entries === null) {
            return [];
        }

        foreach ($entries as $key => $entry) {
            $entries[$key] = IrcUserChannelRelationStorageAdapter::convertToIrcUserChannelRelation($entry);
        }

        return $entries;
    }
}
