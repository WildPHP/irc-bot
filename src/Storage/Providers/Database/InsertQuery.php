<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers\Database;


class InsertQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $table;
    /**
     * @var array
     */
    private $values;

    public function __construct(string $table, array $values)
    {
        $this->table = $table;
        $this->values = $values;
    }

    /**
     * @return string
     * @throws \WildPHP\Core\Storage\StorageException
     */
    public function toString(): string
    {
        $valueQuery = implode(', ', str_split(str_repeat('?', count($this->getValues()))));

        return sprintf('INSERT INTO %s (%s) VALUES (%s)',
            QueryHelper::prepareTableName($this->getTable()),
            implode(', ', QueryHelper::prepareColumnNames(array_keys($this->getValues()))),
            $valueQuery);
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
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }
}