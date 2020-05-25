<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcUserChannelRelation;

class IrcUserChannelRelationStorageAdapter
{
    /**
     * @param IrcUserChannelRelation $relation
     * @return StoredEntity
     */
    public static function convertToStoredEntity(IrcUserChannelRelation $relation): StoredEntity
    {
        return new StoredEntity($relation->toArray());
    }

    /**
     * @param StoredEntityInterface $entity
     * @return IrcUserChannelRelation
     */
    public static function convertToIrcUserChannelRelation(StoredEntityInterface $entity): IrcUserChannelRelation
    {
        return new IrcUserChannelRelation($entity->getData());
    }
}
