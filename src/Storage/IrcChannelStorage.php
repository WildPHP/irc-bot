<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Storage\Providers\StorageProviderInterface;

class IrcChannelStorage implements IrcChannelStorageInterface
{
    /**
     * @var StorageProviderInterface
     */
    private $storageProvider;
    /**
     * @var string
     */
    private $database;

    public function __construct(StorageProviderInterface $storageProvider, string $database = 'channels')
    {
        $this->storageProvider = $storageProvider;
        $this->database = $database;
    }

    /**
     * @param IrcChannel $channel
     */
    public function store(IrcChannel $channel): void
    {
        if (empty($channel->getChannelId())) {
            $this->giveId($channel);
        }

        $this->storageProvider->store($this->database, IrcChannelStorageAdapter::convertToStoredEntity($channel));
    }

    /**
     * @param IrcChannel $channel
     * @throws StorageException
     */
    public function delete(IrcChannel $channel): void
    {
        if (empty($channel->getChannelId()) || !$this->has($channel->getChannelId())) {
            throw new StorageException('Cannot delete channel without ID or channel which is not stored');
        }

        $this->storageProvider->delete($this->database, ['id' => $channel->getChannelId()]);
    }

    /**
     * @param int $channelId
     * @return bool
     */
    public function has(int $channelId): bool
    {
        return $this->storageProvider->has($this->database, ['id' => $channelId]);
    }

    /**
     * @param IrcChannel $channel
     * @return bool
     */
    public function contains(IrcChannel $channel): bool
    {
        return $this->storageProvider->has($this->database, $channel->toArray());
    }

    /**
     * @param int $channelId
     * @return null|IrcChannel
     */
    public function getOne(int $channelId): ?IrcChannel
    {
        $entity = $this->storageProvider->retrieve($this->database, ['id' => $channelId]);

        if ($entity === null) {
            return null;
        }

        return IrcChannelStorageAdapter::convertToIrcChannel($entity);
    }

    /**
     * @param string $name
     * @return null|IrcChannel
     */
    public function getOneByName(string $name): ?IrcChannel
    {
        $entity = $this->storageProvider->retrieve($this->database, ['name' => $name]);

        if ($entity === null) {
            return null;
        }

        return IrcChannelStorageAdapter::convertToIrcChannel($entity);
    }

    /**
     * @param string $name
     * @return IrcChannel
     */
    public function getOrCreateOneByName(string $name): IrcChannel
    {
        $entity = $this->storageProvider->retrieve($this->database, ['name' => $name]);

        if ($entity === null) {
            $ircChannel = new IrcChannel($name);
            $this->store($ircChannel);
            return $ircChannel;
        }

        return IrcChannelStorageAdapter::convertToIrcChannel($entity);
    }

    /**
     * @return IrcChannel[]
     */
    public function getAll(): array
    {
        $entities = $this->storageProvider->retrieveAll($this->database);

        if ($entities === null) {
            return [];
        }

        $channels = [];
        foreach ($entities as $entity) {
            $channels[$entity->getId()] = IrcChannelStorageAdapter::convertToIrcChannel($entity);
        }

        return $channels;
    }

    /**
     * @param IrcChannel $channel
     */
    protected function giveId(IrcChannel $channel): void
    {
        if (!empty($channel->getChannelId())) {
            return;
        }

        $channel->setChannelId((int)@max(array_keys($this->getAll())) + 1);
    }
}
