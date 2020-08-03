<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage\Providers;

use WildPHP\Core\Storage\StoredEntityInterface;

/**
 * Interface DatabaseStorageProviderInterface
 * @package WildPHP\Core\Storage\Providers
 */
interface StorageProviderInterface
{
    /**
     * @param string $database
     * @param StoredEntityInterface $entity
     */
    public function store(string $database, StoredEntityInterface $entity): void;

    /**
     * @param string $database
     * @param array $criteria
     */
    public function delete(string $database, array $criteria): void;

    /**
     * @param string $database
     * @param array $criteria
     * @return mixed
     */
    public function deleteAllWithCriteria(string $database, array $criteria);

    /**
     * @param string $database
     * @param array $criteria
     * @return null|StoredEntityInterface
     */
    public function retrieve(string $database, array $criteria): ?StoredEntityInterface;

    /**
     * @param string $database
     * @param array $criteria
     * @return null|StoredEntityInterface[]
     */
    public function retrieveAll(string $database, array $criteria = []): ?array;

    /**
     * @param string $database
     * @param array $criteria
     * @return bool
     */
    public function has(string $database, array $criteria): bool;
}
