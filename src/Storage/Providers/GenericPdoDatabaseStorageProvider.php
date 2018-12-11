<?php /** @noinspection SyntaxError */

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
     * @param int $limit
     * @return string
     * @throws StorageException
     */
    public function prepareSelectQuery(string $table, array $columns = [], array $where = [], array $joins = [], int $limit = -1)
    {
        return sprintf('SELECT %s FROM %s %s %s %s',
            $this->prepareColumnNames($columns),
            $this->prepareTableName($table),
            $this->prepareJoinStatement($joins),
            $this->prepareWhereStatement($where),
            $limit > 0 ? 'LIMIT ' . $limit : ''
        );
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param array $joins
     * @param int $limit
     * @return array
     * @throws StorageException
     */
    public function select(string $table, array $columns = [], array $where = [], array $joins = [], int $limit = -1): array
    {
        $result = $this->pdo->prepare($this->prepareSelectQuery($table, $columns, $where, $joins, $limit));
        $result->execute(array_values($where));

        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param array $joins
     * @return null|array
     * @throws StorageException
     */
    public function selectFirst(string $table, array $columns = [], array $where = [], array $joins = []): ?array
    {
        return $this->select($table, $columns, $where, $joins, 1)[0] ?? null;
    }

    /**
     * @param string $table
     * @param array $where
     * @return bool
     * @throws StorageException
     */
    public function has(string $table, array $where): bool
    {
        $query = sprintf('SELECT EXISTS(SELECT 1 FROM %s %s LIMIT 1)',
            $this->prepareTableName($table),
            $this->prepareWhereStatement($where)
        );
        $result = $this->pdo->prepare($query);
        $result->execute(array_values($where));
        return $result->fetchColumn() == true;
    }

    /**
     * @param string $table
     * @param array $where
     * @param array $newValues
     * @return string
     * @throws StorageException
     */
    public function prepareUpdateQuery(string $table, array $where, array $newValues): string
    {
        $setQuery = [];
        foreach ($newValues as $column => $value) {
            $setQuery[] = $this->prepareColumnName($column) . ' = ?';
        }

        return sprintf('UPDATE %s SET %s %s',
            $this->prepareTableName($table),
            implode(', ', $setQuery),
            $this->prepareWhereStatement($where)
        );
    }

    /**
     * @param string $table
     * @param array $where
     * @param array $newValues
     * @return int Number of affected rows
     * @throws StorageException
     */
    public function update(string $table, array $where, array $newValues): int
    {
        $result = $this->pdo->prepare($this->prepareUpdateQuery($table, $where, $newValues));
        $result->execute(array_merge(array_values($newValues), array_values($where)));

        return $result->rowCount();
    }

    /**
     * @param string $table
     * @param array $values
     * @return string
     * @throws StorageException
     */
    public function prepareInsertQuery(string $table, array $values)
    {
        $valueQuery = implode(', ', str_split(str_repeat('?', count($values))));

        return sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $this->prepareTableName($table),
            implode(', ', $this->prepareColumnNames(array_keys($values))),
            $valueQuery);
    }

    /**
     * @param string $table
     * @param array $values
     * @return string
     * @throws StorageException
     */
    public function insert(string $table, array $values): string
    {
        $statement = $this->pdo->prepare($this->prepareInsertQuery($table, $values));
        $statement->execute(array_values($values));

        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $where
     * @return string
     * @throws StorageException
     */
    public function prepareDeleteQuery(string $table, array $where)
    {
        return sprintf('DELETE FROM %s %s',
            $this->prepareTableName($table),
            $this->prepareWhereStatement($where)
        );
    }

    /**
     * @param string $table
     * @param array $where
     * @return int Number of affected rows
     * @throws StorageException
     */
    public function delete(string $table, array $where): int
    {
        $statement = $this->pdo->prepare($this->prepareDeleteQuery($table, $where));
        $statement->execute(array_values($where));

        return $statement->rowCount();
    }

    /**
     * @param string $column
     * @return string
     */
    private function prepareColumnName(string $column): string
    {
        if (preg_match('/^\".+\"$/', $column) === 0)
            return $column;

        return '"' . $column . '"';

    }

    /**
     * @param array $columns
     * @return array
     */
    private function prepareColumnNames(array $columns): array
    {
        if (empty($columns)) {
            return ['*'];
        }

        foreach ($columns as $key => $column) {
            $columns[$key] = $this->prepareColumnName($column);
        }

        return $columns;
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