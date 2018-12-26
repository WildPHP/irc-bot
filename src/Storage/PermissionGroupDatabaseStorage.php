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
use WildPHP\Core\Entities\PermissionGroup;
use WildPHP\Core\Storage\Providers\Database\DeleteQuery;
use WildPHP\Core\Storage\Providers\Database\ExistsQuery;
use WildPHP\Core\Storage\Providers\Database\InsertQuery;
use WildPHP\Core\Storage\Providers\Database\QueryHelper;
use WildPHP\Core\Storage\Providers\Database\SelectQuery;
use WildPHP\Core\Storage\Providers\Database\UpdateQuery;
use WildPHP\Core\Storage\Providers\DatabaseStorageProviderInterface;

class PermissionGroupDatabaseStorage implements PermissionGroupStorageInterface
{
    /**
     * @var DatabaseStorageProviderInterface
     */
    private $databaseStorageProvider;

    /**
     * PermissionGroupDatabaseStorage constructor.
     * @param DatabaseStorageProviderInterface $databaseStorageProvider
     */
    public function __construct(DatabaseStorageProviderInterface $databaseStorageProvider)
    {
        $this->databaseStorageProvider = $databaseStorageProvider;
        QueryHelper::addKnownTableName('groups');
        QueryHelper::addKnownTableName('group_channels');
    }

    /**
     * @param PermissionGroup $group
     */
    public function store(PermissionGroup $group): void
    {
        if ($group->getId() > 0 && $this->databaseStorageProvider->has(new ExistsQuery('groups',
                ['id' => $group->getId()]))) {
            $this->update($group);
            return;
        }

        $this->insert($group);
    }

    /**
     * @param PermissionGroup $group
     * @return int ID of the newly inserted row
     */
    private function insert(PermissionGroup $group): int
    {
        $array = $group->toArray();
        unset($array['id']);
        $id = $this->databaseStorageProvider->insert(new InsertQuery('groups', $array));
        $group->setId($id);
        return $id;
    }

    /**
     * @param PermissionGroup $group
     */
    private function update(PermissionGroup $group): void
    {
        $this->databaseStorageProvider->update(new UpdateQuery('groups', ['id' => $group->getId()], $group->toArray()));
    }

    /**
     * @param PermissionGroup $group
     */
    public function delete(PermissionGroup $group): void
    {
        $this->databaseStorageProvider->delete(new DeleteQuery('groups', ['id' => $group->getId()]));
        $group->setId(0);
    }

    /**
     * @param int $id
     * @return null|PermissionGroup
     */
    public function getOne(int $id): ?PermissionGroup
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('groups', [], ['id' => $id]));

        if ($result === null)
            return null;

        return PermissionGroup::fromArray($result);
    }

    /**
     * @param string $name
     * @return null|PermissionGroup
     */
    public function getOneByName(string $name): ?PermissionGroup
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('groups', [], ['name' => $name]));

        if ($result === null)
            return null;

        return PermissionGroup::fromArray($result);
    }

    /**
     * @param string $property
     * @param $value
     * @return null|PermissionGroup
     */
    public function getOneByProperty(string $property, $value): ?PermissionGroup
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('groups', [], [$property => $value]));

        if ($result === null) {
            return null;
        }

        return PermissionGroup::fromArray($result);
    }

    /**
     * @param string $name
     * @return PermissionGroup
     */
    public function getOrCreateOneByName(string $name): PermissionGroup
    {
        $group = $this->getOneByName($name);
        if ($group === null) {
            $group = new PermissionGroup($name);
            $this->store($group);
        }

        return $group;
    }

    /**
     * @param IrcUser $user
     * @return null|PermissionGroup
     */
    public function getOneForUser(IrcUser $user): ?PermissionGroup
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('user_groups', [], ['irc_account' => $user->getIrcAccount()]));

        if ($result === null)
            return null;

        return PermissionGroup::fromArray($result);
    }

    /**
     * @param PermissionGroup $group
     * @return IrcChannel[]
     */
    public function getChannelsForGroup(PermissionGroup $group)
    {
        if (empty($group->getId()))
            return [];

        $result = $this->databaseStorageProvider->select(new SelectQuery('channels',
            ['channels.*'],
            ['group_id' => $group->getId()],
            ['group_channels' => 'channels.id = group_channels.channel_id']
        ));

        if (empty($result))
            return [];

        $channels = [];
        foreach ($result as $item) {
            $channels[] = IrcChannel::fromArray($item);
        }

        return $channels;
    }
}