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

class IrcUserDatabaseStorage implements IrcUserStorageInterface
{
    public static const TABLE = 'users';

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
    }

    /**
     * @param IrcUser $user
     */
    public function store(IrcUser $user): void
    {
        // TODO: Implement store() method.
    }

    /**
     * @param IrcUser $user
     */
    public function delete(IrcUser $user): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param int $id
     * @return null|IrcUser
     */
    public function getOne(int $id): ?IrcUser
    {
        // TODO: Implement getOne() method.
    }

    /**
     * @param string $nickname
     * @return null|IrcUser
     */
    public function getOneByNickname(string $nickname): ?IrcUser
    {
        // TODO: Implement getOneByNickname() method.
    }

    /**
     * @param string $property
     * @param $value
     * @return null|IrcUser
     */
    public function getOneByProperty(string $property, $value): ?IrcUser
    {
        // TODO: Implement getOneByProperty() method.
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
}