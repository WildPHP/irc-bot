<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers\Database;


use WildPHP\Core\Storage\StorageException;

class QueryHelper
{
    /**
     * @var array
     */
    private static $knownTables = [];

    /**
     * @param string $column
     * @return string
     */
    public static function prepareColumnName(string $column): string
    {
        if (preg_match('/^\".+\"$/', $column) === 0) {
            return $column;
        }

        return '"' . $column . '"';

    }

    /**
     * @param array $columns
     * @return array
     */
    public static function prepareColumnNames(array $columns): array
    {
        if (empty($columns)) {
            return ['*'];
        }

        foreach ($columns as $key => $column) {
            $columns[$key] = self::prepareColumnName($column);
        }

        return $columns;
    }

    /**
     * @param array $joins
     * @return string
     * @throws StorageException
     */
    public static function prepareJoinStatement(array $joins): string
    {
        if (empty($joins)) {
            return '';
        }

        $statements = [];
        foreach ($joins as $table => $joinOn) {
            $statements[] = sprintf('JOIN %s ON %s',
                self::prepareTableName($table),
                $joinOn);
        }

        return implode(' ', $statements);
    }

    /**
     * @param array $where
     * @return string
     */
    public static function prepareWhereStatement(array $where): string
    {
        if (empty($where)) {
            return '';
        }

        $statements = [];
        foreach ($where as $column => $value) {
            $statements[] = sprintf('%s = ?', $column);
        }

        return 'WHERE ' . implode(' AND ', $statements);
    }

    /**
     * @param string $table
     * @return string
     * @throws StorageException
     */
    public static function prepareTableName(string $table): string
    {
        if (!in_array($table, self::$knownTables)) {
            throw new StorageException('Table is not in the known tables list.');
        }

        return $table;
    }

    /**
     * @param string $table
     */
    public static function addKnownTableName(string $table)
    {
        if (!in_array($table, self::$knownTables)) {
            self::$knownTables[] = $table;
        }
    }
}