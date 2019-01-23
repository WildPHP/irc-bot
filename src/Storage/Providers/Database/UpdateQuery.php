<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers\Database;


class UpdateQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $where;

    /**
     * @var array
     */
    private $newValues;

    /**
     * UpdateQuery constructor.
     * @param string $table
     * @param array $where
     * @param array $newValues
     */
    public function __construct(string $table, array $where, array $newValues)
    {
        $this->table = $table;
        $this->where = $where;
        $this->newValues = $newValues;
    }

    /**
     * @return string
     * @throws \WildPHP\Core\Storage\StorageException
     */
    public function toString(): string
    {
        $setQuery = [];
        foreach ($this->getNewValues() as $column => $value) {
            $setQuery[] = QueryHelper::prepareColumnName($column) . ' = ?';
        }

        /** @noinspection SyntaxError */
        return sprintf('UPDATE %s SET %s %s',
            QueryHelper::prepareTableName($this->getTable()),
            implode(', ', $setQuery),
            QueryHelper::prepareWhereStatement($this->getWhere())
        );
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * @return array
     */
    public function getWhere(): array
    {
        return $this->where;
    }

    /**
     * @param array $where
     */
    public function setWhere(array $where): void
    {
        $this->where = $where;
    }

    /**
     * @return array
     */
    public function getNewValues(): array
    {
        return $this->newValues;
    }

    /**
     * @param array $newValues
     */
    public function setNewValues(array $newValues): void
    {
        $this->newValues = $newValues;
    }
}