<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;


use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Entities\ModeGroup;
use WildPHP\Core\Entities\PermissionGroup;
use WildPHP\Core\Entities\Policy;
use WildPHP\Core\Storage\Providers\Database\DeleteQuery;
use WildPHP\Core\Storage\Providers\Database\ExistsQuery;
use WildPHP\Core\Storage\Providers\Database\InsertQuery;
use WildPHP\Core\Storage\Providers\Database\QueryHelper;
use WildPHP\Core\Storage\Providers\Database\SelectQuery;
use WildPHP\Core\Storage\Providers\Database\UpdateQuery;
use WildPHP\Core\Storage\Providers\DatabaseStorageProviderInterface;

class PolicyDatabaseStorage implements PolicyStorageInterface
{
    /**
     * @var DatabaseStorageProviderInterface
     */
    private $databaseStorageProvider;

    /**
     * PolicyDatabaseStorage constructor.
     * @param DatabaseStorageProviderInterface $databaseStorageProvider
     */
    public function __construct(DatabaseStorageProviderInterface $databaseStorageProvider)
    {
        $this->databaseStorageProvider = $databaseStorageProvider;
        QueryHelper::addKnownTableName('policies');
        QueryHelper::addKnownTableName('group_policies');
        QueryHelper::addKnownTableName('mode_group_policies');
        QueryHelper::addKnownTableName('user_policies');
    }

    /**
     * @param Policy $policy
     */
    public function store(Policy $policy): void
    {
        if ($policy->getId() > 0 && $this->databaseStorageProvider->has(new ExistsQuery('policies',
                ['id' => $policy->getId()]))) {
            $this->update($policy);
            return;
        }

        $this->insert($policy);
    }

    /**
     * @param Policy $policy
     * @return int ID of the newly inserted row
     */
    private function insert(Policy $policy): int
    {
        $array = $policy->toArray();
        unset($array['id']);
        $id = $this->databaseStorageProvider->insert(new InsertQuery('policies', $array));
        $policy->setId($id);
        return $id;
    }

    /**
     * @param Policy $policy
     */
    private function update(Policy $policy): void
    {
        $this->databaseStorageProvider->update(new UpdateQuery('policies', ['id' => $policy->getId()], $policy->toArray()));
    }

    /**
     * @param Policy $policy
     */
    public function delete(Policy $policy): void
    {
        $this->databaseStorageProvider->delete(new DeleteQuery('policies', ['id' => $policy->getId()]));
        $policy->setId(0);
    }

    /**
     * @param int $id
     * @return null|Policy
     */
    public function getOne(int $id): ?Policy
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('policies', [], ['id' => $id]));

        if ($result === null)
            return null;

        return Policy::fromArray($result);
    }

    /**
     * @param string $name
     * @return null|Policy
     */
    public function getOneByName(string $name): ?Policy
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('policies', [], ['name' => $name]));

        if ($result === null)
            return null;

        return Policy::fromArray($result);
    }

    /**
     * @param string $property
     * @param $value
     * @return null|Policy
     */
    public function getOneByProperty(string $property, $value): ?Policy
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('policies', [], [$property => $value]));

        if ($result === null) {
            return null;
        }

        return Policy::fromArray($result);
    }

    /**
     * @param string $name
     * @return Policy
     */
    public function getOrCreateOneByNickname(string $name): Policy
    {
        $policy = $this->getOneByName($name);
        if ($policy === null) {
            $policy = new Policy($name);
            $this->store($policy);
        }

        return $policy;
    }

    /**
     * @param IrcUser $user
     * @return Policy[]
     */
    public function getAllForUser(IrcUser $user)
    {
        if (empty($user->getIrcAccount()))
            return [];

        $result = $this->databaseStorageProvider->select(new SelectQuery('policies',
            ['policies.*'],
            ['irc_account' => $user->getIrcAccount()],
            ['user_policies' => 'policies.id = user_policies.policy_id']
        ));

        if (empty($result))
            return [];

        $policies = [];
        foreach ($result as $item) {
            $policies[] = Policy::fromArray($item);
        }

        return $policies;
    }

    /**
     * @param PermissionGroup $group
     * @return Policy[]
     */
    public function getAllForGroup(PermissionGroup $group): array
    {
        if ($group->getId() == 0)
            return [];

        $result = $this->databaseStorageProvider->select(new SelectQuery('policies',
            ['policies.*'],
            ['group_id' => $group->getId()],
            ['group_policies' => 'policies.id = group_policies.policy_id']
        ));

        if (empty($result))
            return [];

        $policies = [];
        foreach ($result as $item) {
            $policies[] = Policy::fromArray($item);
        }

        return $policies;
    }

    /**
     * @param ModeGroup $group
     * @return Policy[]
     */
    public function getAllForModeGroup(ModeGroup $group): array
    {
        if (empty($group->getMode()))
            return [];

        $result = $this->databaseStorageProvider->select(new SelectQuery('policies',
            ['policies.*'],
            ['mode' => $group->getMode()],
            ['mode_group_policies' => 'policies.id = mode_group_policies.policy_id']
        ));

        if (empty($result))
            return [];

        $policies = [];
        foreach ($result as $item) {
            $policies[] = Policy::fromArray($item);
        }

        return $policies;
    }
}