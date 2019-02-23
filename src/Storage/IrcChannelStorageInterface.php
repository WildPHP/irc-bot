<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;


use WildPHP\Core\Entities\IrcChannel;

interface IrcChannelStorageInterface
{
    /**
     * @param IrcChannel $channel
     */
    public function store(IrcChannel $channel): void;

    /**
     * @param IrcChannel $channel
     */
    public function delete(IrcChannel $channel): void;

    /**
     * @param int $id
     * @return bool
     */
    public function has(int $id): bool;

    /**
     * @param IrcChannel $channel
     * @return bool
     */
    public function contains(IrcChannel $channel): bool;

    /**
     * @param int $id
     * @return null|IrcChannel
     */
    public function getOne(int $id): ?IrcChannel;

    /**
     * @param string $name
     * @return null|IrcChannel
     */
    public function getOneByName(string $name): ?IrcChannel;

    /**
     * @param string $name
     * @return IrcChannel
     */
    public function getOrCreateOneByName(string $name): IrcChannel;

    /**
     * @return IrcChannel[]
     */
    public function getAll(): array;
}