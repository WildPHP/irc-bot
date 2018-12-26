<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;


use WildPHP\Core\Entities\ModeGroup;
use WildPHP\Core\Storage\Providers\Database\DeleteQuery;
use WildPHP\Core\Storage\Providers\Database\ExistsQuery;
use WildPHP\Core\Storage\Providers\Database\InsertQuery;
use WildPHP\Core\Storage\Providers\Database\QueryHelper;
use WildPHP\Core\Storage\Providers\Database\SelectQuery;
use WildPHP\Core\Storage\Providers\Database\UpdateQuery;
use WildPHP\Core\Storage\Providers\DatabaseStorageProviderInterface;

class ModeGroupDatabaseStorage implements ModeGroupStorageInterface
{
    /**
     * @var DatabaseStorageProviderInterface
     */
    private $databaseStorageProvider;

    /**
     * ModeGroupDatabaseStorage constructor.
     * @param DatabaseStorageProviderInterface $databaseStorageProvider
     */
    public function __construct(DatabaseStorageProviderInterface $databaseStorageProvider)
    {
        $this->databaseStorageProvider = $databaseStorageProvider;
        QueryHelper::addKnownTableName('mode_groups');
    }

    /**
     * @param ModeGroup $group
     */
    public function store(ModeGroup $group): void
    {
        if ($group->getMode() > 0 && $this->databaseStorageProvider->has(new ExistsQuery('mode_groups',
                ['mode' => $group->getMode()]))) {
            $this->update($group);
            return;
        }

        $this->insert($group);
    }

    /**
     * @param ModeGroup $group
     * @return int ID of the newly inserted row
     */
    private function insert(ModeGroup $group): int
    {
        $array = $group->toArray();
        $this->databaseStorageProvider->insert(new InsertQuery('mode_groups', $array));
        return $group->getMode();
    }

    /**
     * @param ModeGroup $group
     */
    private function update(ModeGroup $group): void
    {
        $this->databaseStorageProvider->update(new UpdateQuery('mode_groups', ['mode' => $group->getMode()],
            $group->toArray()));
    }

    /**
     * @param ModeGroup $group
     */
    public function delete(ModeGroup $group): void
    {
        $this->databaseStorageProvider->delete(new DeleteQuery('mode_groups', ['mode' => $group->getMode()]));
        $group->setMode(0);
    }

    /**
     * @param string $mode
     * @return null|ModeGroup
     */
    public function getOneByMode(string $mode): ?ModeGroup
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('mode_groups', [], ['mode' => $mode]));

        if ($result === null) {
            return null;
        }

        return ModeGroup::fromArray($result);
    }

    /**
     * @param string $mode
     * @return ModeGroup
     */
    public function getOrCreateOneByMode(string $mode): ModeGroup
    {
        $group = $this->getOneByMode($mode);
        if ($group === null) {
            $group = new ModeGroup($mode);
            $this->store($group);
        }

        return $group;
    }
}