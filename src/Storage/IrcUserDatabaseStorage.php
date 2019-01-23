<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Storage\Providers\Database\DeleteQuery;
use WildPHP\Core\Storage\Providers\Database\ExistsQuery;
use WildPHP\Core\Storage\Providers\Database\InsertQuery;
use WildPHP\Core\Storage\Providers\Database\QueryHelper;
use WildPHP\Core\Storage\Providers\Database\SelectQuery;
use WildPHP\Core\Storage\Providers\Database\UpdateQuery;
use WildPHP\Core\Storage\Providers\DatabaseStorageProviderInterface;

class IrcUserDatabaseStorage implements IrcUserStorageInterface
{
    /**
     * @var DatabaseStorageProviderInterface
     */
    private $databaseStorageProvider;

    /**
     * IrcUserDatabaseStorage constructor.
     * @param DatabaseStorageProviderInterface $databaseStorageProvider
     */
    public function __construct(DatabaseStorageProviderInterface $databaseStorageProvider)
    {
        $this->databaseStorageProvider = $databaseStorageProvider;
        QueryHelper::addKnownTableName('users');
    }

    /**
     * @param IrcUser $user
     */
    public function store(IrcUser $user): void
    {
        if ($user->getId() > 0 && $this->databaseStorageProvider->has(new ExistsQuery('users',
                ['id' => $user->getId()]))) {
            $this->update($user);
            return;
        }

        $this->insert($user);
    }

    /**
     * @param IrcUser $user
     * @return int ID of the newly inserted row
     */
    private function insert(IrcUser $user): int
    {
        $array = $user->toArray();
        unset($array['id']);
        $id = $this->databaseStorageProvider->insert(new InsertQuery('users', $array));
        $user->setId($id);
        return $id;
    }

    /**
     * @param IrcUser $user
     */
    private function update(IrcUser $user): void
    {
        $this->databaseStorageProvider->update(new UpdateQuery('users', ['id' => $user->getId()], $user->toArray()));
    }

    /**
     * @param IrcUser $user
     */
    public function delete(IrcUser $user): void
    {
        $this->databaseStorageProvider->delete(new DeleteQuery('users', ['id' => $user->getId()]));
        $user->setId(0);
    }

    /**
     * @param int $id
     * @return null|IrcUser
     */
    public function getOne(int $id): ?IrcUser
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('users', [], ['id' => $id]));

        if ($result === null)
            return null;

        return IrcUser::fromArray($result);
    }

    /**
     * @param string $nickname
     * @return null|IrcUser
     */
    public function getOneByNickname(string $nickname): ?IrcUser
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('users', [], ['nickname' => $nickname]));

        if ($result === null)
            return null;

        return IrcUser::fromArray($result);
    }

    /**
     * @param string $property
     * @param $value
     * @return null|IrcUser
     */
    public function getOneByProperty(string $property, $value): ?IrcUser
    {
        $result = $this->databaseStorageProvider->selectFirst(new SelectQuery('users', [], [$property => $value]));

        if ($result === null) {
            return null;
        }

        return IrcUser::fromArray($result);
    }

    /**
     * @param int $userId
     * @return IrcChannel[]
     */
    public function getRelatedChannels(int $userId): array
    {
        // TODO: Implement getRelatedChannels() method.
    }

    /**
     * @param string $nickname
     * @return IrcChannel[]
     */
    public function getRelatedChannelsByNickname(string $nickname): array
    {
        // TODO: Implement getRelatedChannelsByNickname() method.
    }

    /**
     * @param string $nickname
     * @return IrcUser
     */
    public function getOrCreateOneByNickname(string $nickname): IrcUser
    {
        $user = $this->getOneByNickname($nickname);
        if ($user === null) {
            $user = new IrcUser($nickname);
            $this->store($user);
        }

        return $user;
    }
}