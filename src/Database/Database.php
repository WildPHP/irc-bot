<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Database;

use Medoo\Medoo;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

/**
 * Class Database
 * @package WildPHP\Core\Database
 *
 * For now, wrapper around the Medoo methods. It's here so that if we ever
 * decide to change our database system, things will not need rewriting.
 */
class Database implements ComponentInterface
{
    use ComponentTrait;

    /**
     * @var Medoo
     */
    private $medoo;

    /**
     * Database constructor.
     * @param Medoo $medoo
     */
    public function __construct(Medoo $medoo)
    {
        $this->medoo = $medoo;
    }

    /**
     * @param string $table
     * @param array $join
     * @param array|string $columns
     * @param array|null $where
     * @return array|bool
     */
    public function select(string $table, array $join, $columns, array $where = null)
    {
        return $this->medoo->select($table, $join, $columns, $where);
    }

    /**
     * @param string $table
     * @param array $datas
     * @return bool|\PDOStatement
     */
    public function insert(string $table, array $datas)
    {
        return $this->medoo->insert($table, $datas);
    }

    /**
     * @param string $table
     * @param array $data
     * @param array|null $where
     * @return bool|\PDOStatement
     */
    public function update(string $table, array $data, array $where = null)
    {
        return $this->medoo->update($table, $data, $where);
    }

    /**
     * @param string $table
     * @param array $where
     * @return bool|\PDOStatement
     */
    public function delete(string $table, array $where)
    {
        return $this->medoo->delete($table, $where);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array|null $where
     * @return bool|\PDOStatement
     */
    public function replace(string $table, array $columns, array $where = null)
    {
        return $this->medoo->replace($table, $columns, $where);
    }

    /**
     * @param string $table
     * @param array $join
     * @param array|string $columns
     * @param array $where
     * @return array|bool|mixed
     */
    public function get(string $table, array $join, $columns, array $where)
    {
        return $this->medoo->get($table, $join, $columns, $where);
    }

    /**
     * @param string $table
     * @param array $join
     * @param array $where
     * @return bool
     */
    public function has(string $table, array $join, array $where)
    {
        return $this->medoo->has($table, $join, $where);
    }

    /**
     * @param string $table
     * @param array $join
     * @param string $column
     * @param array|null $where
     * @return mixed
     */
    public function count(string $table, array $join, string $column, array $where = null)
    {
        return $this->medoo->__call('count', [$table, $join, $column, $where]);
    }

    /**
     * @param string $table
     * @param array $join
     * @param string $column
     * @param array|null $where
     * @return mixed
     */
    public function min(string $table, array $join, string $column, array $where = null)
    {
        return $this->medoo->__call('min', [$table, $join, $column, $where]);
    }

    /**
     * @param string $table
     * @param array $join
     * @param string $column
     * @param array|null $where
     * @return mixed
     */
    public function max(string $table, array $join, string $column, array $where = null)
    {
        return $this->medoo->__call('max', [$table, $join, $column, $where]);
    }

    /**
     * @param string $table
     * @param array $join
     * @param string $column
     * @param array|null $where
     * @return mixed
     */
    public function avg(string $table, array $join, string $column, array $where = null)
    {
        return $this->medoo->__call('avg', [$table, $join, $column, $where]);
    }

    /**
     * @param string $table
     * @param array $join
     * @param string $column
     * @param array|null $where
     * @return mixed
     */
    public function sum(string $table, array $join, string $column, array $where = null)
    {
        return $this->medoo->__call('sum', [$table, $join, $column, $where]);
    }

    /**
     * @return int|mixed|string
     */
    public function id()
    {
        return $this->medoo->id();
    }

    /**
     * @param string $query
     * @param array|null $map
     * @return bool|\PDOStatement
     */
    public function query(string $query, array $map = null)
    {
        return $this->medoo->query($query, $map);
    }

    /**
     * @param string $string
     * @return string
     */
    public function quote(string $string)
    {
        return $this->medoo->quote($string);
    }
}