<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers;

use WildPHP\Core\Storage\StorageException;

class GenericPdoDatabaseStorageProvider implements DatabaseStorageProviderInterface
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $knownTables = ['users', 'channels', 'policies'];

    /**
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param array $joins
     * @return array
     * @throws StorageException
     */
    public function select(string $table, array $columns = [], array $where = [], array $joins = []): array
    {
        $query = sprintf('SELECT %s FROM %s %s %s',
            $this->prepareColumnNames($columns),
            $this->prepareTableName($table),
            $this->prepareJoinStatement($joins),
            $this->prepareWhereStatement($where)
        );
        $result = $this->pdo->prepare($query, array_values($where));

        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param array $joins
     * @return array
     * @throws StorageException
     */
    public function selectFirst(string $table, array $columns = [], array $where = [], array $joins = []): array
    {
        $query = sprintf('SELECT %s FROM %s %s %s LIMIT 1',
            $this->prepareColumnNames($columns),
            $this->prepareTableName($table),
            $this->prepareJoinStatement($joins),
            $this->prepareWhereStatement($where)
        );
        $statement = $this->pdo->prepare($query);
        $statement->execute(array_values($where));

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param array $where
     * @param array $newValues
     * @return mixed
     */
    public function update(string $table, array $where, array $newValues)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param string $table
     * @param array $values
     * @return int
     * @throws StorageException
     */
    public function insert(string $table, array $values): string
    {
        $valueQuery = implode(', ', str_split(str_repeat('?', count($values))));

        $query = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $this->prepareTableName($table),
            $this->prepareColumnNames(array_keys($values)),
            $valueQuery);

        $statement = $this->pdo->prepare($query);
        $statement->execute(array_values($values));

        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $where
     * @return mixed
     * @throws StorageException
     */
    public function delete(string $table, array $where)
    {
        $query = sprintf('DELETE FROM %s %s',
            $this->prepareTableName($table),
            $this->prepareWhereStatement($where)
        );
        $statement = $this->pdo->prepare($query);
        $statement->execute(array_values($where));
    }

    /**
     * @param array $columns
     * @return string
     */
    private function prepareColumnNames(array $columns): string
    {
        if (empty($columns)) {
            return '*';
        }

        $statements = [];
        foreach ($columns as $column) {
            $statements[] = $this->pdo->quote($column);
        }

        return implode(',', $statements);
    }

    /**
     * @param array $joins
     * @return string
     * @throws StorageException
     */
    private function prepareJoinStatement(array $joins): string
    {
        if (empty($joins)) {
            return '';
        }

        $statements = [];
        foreach ($joins as $table => $joinOn) {
            $statements[] = sprintf('JOIN %s ON %s',
                $this->prepareTableName($table),
                $joinOn);
        }

        return implode(' ', $statements);
    }

    /**
     * @param array $where
     * @return string
     */
    private function prepareWhereStatement(array $where): string
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
    private function prepareTableName(string $table): string
    {
        if (!in_array($table, $this->knownTables)) {
            throw new StorageException('Table is not in the known tables list.');
        }

        return $table;
    }

    /**
     * @param string $table
     */
    public function addKnownTableName(string $table)
    {
        if (!in_array($table, $this->knownTables)) {
            $this->knownTables[] = $table;
        }
    }
}