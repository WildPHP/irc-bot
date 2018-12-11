<?php /** @noinspection SyntaxError */

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
use WildPHP\Core\Storage\StorageException;

abstract class GenericPdoDatabaseStorageProvider implements DatabaseStorageProviderInterface
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
     * @param SelectQuery $query
     * @return array
     * @throws StorageException
     */
    public function select(SelectQuery $query): array
    {
        $result = $this->pdo->prepare($query->toString());
        $result->execute(array_values($query->getWhere()));

        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param SelectQuery $query
     * @return null|array
     * @throws StorageException
     */
    public function selectFirst(SelectQuery $query): ?array
    {
        $query->setLimit(1);
        return $this->select($query)[0] ?? null;
    }

    /**
     * @param ExistsQuery $query
     * @return bool
     * @throws StorageException
     */
    public function has(ExistsQuery $query): bool
    {
        $result = $this->pdo->prepare($query->toString());
        $result->execute(array_values($query->getWhere()));
        return $result->fetchColumn() == 1;
    }

    /**
     * @param UpdateQuery $query
     * @return int Number of affected rows
     * @throws StorageException
     */
    public function update(UpdateQuery $query): int
    {
        $result = $this->pdo->prepare($query->toString());
        $result->execute(array_merge(array_values($query->getNewValues()), array_values($query->getWhere())));

        return $result->rowCount();
    }

    /**
     * @param InsertQuery $query
     * @return string
     * @throws StorageException
     */
    public function insert(InsertQuery $query): string
    {
        $statement = $this->pdo->prepare($query->toString());
        $statement->execute(array_values($query->getValues()));

        return $this->pdo->lastInsertId();
    }

    /**
     * @param DeleteQuery $query
     * @return int Number of affected rows
     * @throws StorageException
     */
    public function delete(DeleteQuery $query): int
    {
        $statement = $this->pdo->prepare($query->toString());
        $statement->execute(array_values($query->getWhere()));

        return $statement->rowCount();
    }
}