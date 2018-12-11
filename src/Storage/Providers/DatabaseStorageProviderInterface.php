<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers;

/**
 * Interface DatabaseStorageProviderInterface
 * @package WildPHP\Core\Storage\Providers
 */
interface DatabaseStorageProviderInterface
{
    /**
     * @param string $table
     * @param array $where
     * @param array $joins
     * @return array
     */
    public function select(string $table, array $where = [], array $joins = []): array;

    /**
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param array $joins
     * @return array|null
     */
    public function selectFirst(string $table, array $columns = [], array $where = [], array $joins = []): ?array;

    /**
     * @param string $table
     * @param array $where
     * @param array $newValues
     * @return mixed
     */
    public function update(string $table, array $where, array $newValues);

    /**
     * @param string $table
     * @param array $values
     * @return string
     */
    public function insert(string $table, array $values): string;

    /**
     * @param string $table
     * @param array $where
     * @return mixed
     */
    public function delete(string $table, array $where);

    /**
     * @param string $table
     * @param array $where
     * @return bool
     */
    public function has(string $table, array $where): bool;

    /**
     * @param string $tableName
     * @return mixed
     */
    public function addKnownTableName(string $tableName);
}