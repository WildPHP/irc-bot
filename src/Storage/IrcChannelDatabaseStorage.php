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
        $databaseStorageProvider->addKnownTableName('channels');
    }

    /**
     * @param IrcChannel $channel
     */
    public function store(IrcChannel $channel): void
    {
        if ($channel->getId() != 0 && !$this->databaseStorageProvider->has('channels', ['id' => $channel->getId()])) {
            $this->update($channel);
            return;
        }

        $this->insert($channel);
    }

    /**
     * @param IrcChannel $channel
     * @return int ID of the newly inserted row
     */
    private function insert(IrcChannel $channel): int
    {
        $array = $channel->toArray();
        unset($array['id']);
        $id = $this->databaseStorageProvider->insert('channels', $array);
        $channel->setId($id);
        return $id;
    }

    /**
     * @param IrcChannel $channel
     */
    private function update(IrcChannel $channel): void
    {
        $this->databaseStorageProvider->update('channels', ['id' => $channel->getId()], $channel->toArray());
    }

    /**
     * @param IrcChannel $channel
     */
    public function delete(IrcChannel $channel): void
    {
        $this->databaseStorageProvider->delete('channels', ['id' => $channel->getId()]);
        $channel->setId(0);
    }

    /**
     * @param int $id
     * @return null|IrcChannel
     */
    public function getOne(int $id): ?IrcChannel
    {
        $result = $this->databaseStorageProvider->selectFirst('channels', [], ['id' => $id]);

        if ($result === null)
            return null;

        return IrcChannel::fromArray($result);
    }

    /**
     * @param string $name
     * @return null|IrcChannel
     */
    public function getOneByName(string $name): ?IrcChannel
    {
        $result = $this->databaseStorageProvider->selectFirst('channels', [], ['name' => $name]);

        if ($result === null)
            return null;

        return IrcChannel::fromArray($result);
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