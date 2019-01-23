<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers\Database;


class SelectQuery implements QueryInterface
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
    private $joins;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var int
     */
    private $limit;

    public function __construct(
        string $table,
        array $columns = [],
        array $where = [],
        array $joins = [],
        int $limit = -1
    ) {
        $this->table = $table;
        $this->where = $where;
        $this->joins = $joins;
        $this->columns = $columns;
        $this->limit = $limit;
    }

    /**
     * @return string
     * @throws \WildPHP\Core\Storage\StorageException
     */
    public function toString(): string
    {
        $limit = $this->getLimit() > 0 ? 'LIMIT ' . $this->getLimit() : '';
        /** @noinspection SyntaxError */
        return sprintf('SELECT %s FROM %s %s %s %s',
            implode(', ', QueryHelper::prepareColumnNames($this->getColumns())),
            QueryHelper::prepareTableName($this->getTable()),
            QueryHelper::prepareJoinStatement($this->getJoins()),
            QueryHelper::prepareWhereStatement($this->getWhere()),
            $limit
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
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @param array $joins
     */
    public function setJoins(array $joins): void
    {
        $this->joins = $joins;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return !empty($this->columns) ? $this->columns : ['*'];
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}