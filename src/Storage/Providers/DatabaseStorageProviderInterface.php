<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers;

use WildPHP\Core\Storage\Providers\Database\DeleteQuery;
use WildPHP\Core\Storage\Providers\Database\ExistsQuery;
use WildPHP\Core\Storage\Providers\Database\InsertQuery;
use WildPHP\Core\Storage\Providers\Database\SelectQuery;
use WildPHP\Core\Storage\Providers\Database\UpdateQuery;

/**
 * Interface DatabaseStorageProviderInterface
 * @package WildPHP\Core\Storage\Providers
 */
interface DatabaseStorageProviderInterface
{
    /**
     * @param SelectQuery $query
     * @return array
     */
    public function select(SelectQuery $query): array;

    /**
     * @param SelectQuery $query
     * @return array|null
     */
    public function selectFirst(SelectQuery $query): ?array;

    /**
     * @param UpdateQuery $query
     * @return mixed
     */
    public function update(UpdateQuery $query);

    /**
     * @param InsertQuery $query
     * @return string
     */
    public function insert(InsertQuery $query): string;

    /**
     * @param DeleteQuery $query
     * @return mixed
     */
    public function delete(DeleteQuery $query);

    /**
     * @param ExistsQuery $query
     * @return bool
     */
    public function has(ExistsQuery $query): bool;
}