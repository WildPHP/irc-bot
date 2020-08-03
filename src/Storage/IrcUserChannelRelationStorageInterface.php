<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage;

use WildPHP\Core\Entities\IrcUserChannelRelation;

interface IrcUserChannelRelationStorageInterface
{
    /**
     * @param IrcUserChannelRelation $relation
     */
    public function store(IrcUserChannelRelation $relation): void;

    /**
     * @param IrcUserChannelRelation $relation
     */
    public function delete(IrcUserChannelRelation $relation): void;

    /**
     * @param IrcUserChannelRelation $relation
     * @return bool
     */
    public function contains(IrcUserChannelRelation $relation): bool;

    /**
     * @param int $userId
     * @param int $channelId
     * @return bool
     */
    public function has(int $userId, int $channelId): bool;

    /**
     * @param int $userId
     * @param int $channelId
     * @return null|IrcUserChannelRelation
     */
    public function getOne(int $userId, int $channelId): ?IrcUserChannelRelation;

    /**
     * @param int $userId
     * @param int $channelId
     * @return IrcUserChannelRelation
     */
    public function getOrCreateOne(int $userId, int $channelId): IrcUserChannelRelation;

    /**
     * @param int $userId
     * @return null|IrcUserChannelRelation[]
     */
    public function getByUserId(int $userId): ?array;

    /**
     * @param int $channelId
     * @return null|IrcUserChannelRelation[]
     */
    public function getByChannelId(int $channelId): ?array;

    /**
     * @return IrcUserChannelRelation[]
     */
    public function getAll(): array;
}
