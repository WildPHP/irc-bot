<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcUser;

class IrcUserStorageAdapter
{
    /**
     * @param IrcUser $user
     * @return StoredEntity
     */
    public static function convertToStoredEntity(IrcUser $user): StoredEntity
    {
        return new StoredEntity($user->toArray(), $user->id);
    }

    /**
     * @param StoredEntityInterface $entity
     * @return IrcUser
     */
    public static function convertToIrcUser(StoredEntityInterface $entity): IrcUser
    {
        return new IrcUser($entity->getData());
    }
}
