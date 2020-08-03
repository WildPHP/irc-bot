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

class MemoryStorageProvider extends BaseStorageProvider
{
    protected $cache = [];

    public function store(string $database, StoredEntityInterface $entity): void
    {
        $cache = &$this->openDatabase($database);

        if (!empty($entity->getId())) {
            $cache[$entity->getId()] = $entity->getData();
        } else {
            $cache[] = $entity->getData();
        }
    }

    public function delete(string $database, array $criteria): void
    {
        $cache = &$this->openDatabase($database);
        $entries = self::matchEntries($cache, $criteria);

        $ids = array_keys($entries);
        $entryId = reset($ids);
        unset($cache[$entryId]);
    }

    public function deleteAllWithCriteria(string $database, array $criteria)
    {
        $cache = &$this->openDatabase($database);
        $entries = self::matchEntries($cache, $criteria);

        foreach (array_keys($entries) as $entryId) {
            unset($cache[$entryId]);
        }

        return count($entries);
    }

    public function retrieve(string $database, array $criteria): ?StoredEntityInterface
    {
        if (empty($criteria) || !$this->has($database, $criteria)) {
            return null;
        }

        $cache = &$this->openDatabase($database);
        $entries = self::matchEntries($cache, $criteria);

        return $this->prepareEntry(reset($entries));
    }

    public function retrieveAll(string $database, array $criteria = []): ?array
    {
        if (empty($criteria) || !$this->has($database, $criteria)) {
            return null;
        }

        $cache = &$this->openDatabase($database);
        $entries = self::matchEntries($cache, $criteria);
        return $this->prepareEntries($entries);
    }

    public function has(string $database, array $criteria): bool
    {
        $cache = &$this->openDatabase($database);
        $entries = self::matchEntries($cache, $criteria);
        return !empty(self::matchEntries($entries, $criteria));
    }

    protected function &openDatabase(string $database)
    {
        if (!array_key_exists($database, $this->cache)) {
            $this->cache[$database] = [];
        }

        return $this->cache[$database];
    }
}
