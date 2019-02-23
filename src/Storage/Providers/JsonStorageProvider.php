<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers;

use WildPHP\Core\Storage\StorageException;
use WildPHP\Core\Storage\StoredEntity;
use WildPHP\Core\Storage\StoredEntityInterface;

class JsonStorageProvider implements StorageProviderInterface
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * 'database' => ['entry', 'entry', ...]
     * @var array
     */
    private $cache = [];

    public function __construct(string $baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * @param string $database
     * @param StoredEntityInterface $entity
     * @throws StorageException
     */
    public function store(string $database, StoredEntityInterface $entity): void
    {
        $this->openDatabase($database);

        $cache = &$this->cache[$database];

        if (!empty($entity->getId())) {
            $cache[$entity->getId()] = $entity->getData();
        }
        else {
            $cache[] = $entity->getData();
        }

        $this->syncDatabase($database);
    }

    /**
     * @param string $database
     * @param array $criteria
     * @throws StorageException
     */
    public function delete(string $database, array $criteria): void
    {
        $this->openDatabase($database);
        $entries = $this->getEntriesWithCriteria($database, $criteria);

        $cache = &$this->cache[$database];

        $ids = array_keys($entries);
        $id = reset($ids);
        unset($cache[$id]);

        $this->syncDatabase($database);
    }

    /**
     * @param string $database
     * @param array $criteria
     * @return int number of rows deleted
     * @throws StorageException
     */
    public function deleteAllWithCriteria(string $database, array $criteria): int
    {
        $this->openDatabase($database);
        $entries = $this->getEntriesWithCriteria($database, $criteria);

        $cache = &$this->cache[$database];

        foreach ($entries as $id => $entry) {
            unset($cache[$id]);
        }

        $this->syncDatabase($database);

        return count($entries);
    }

    /**
     * @param string $database
     * @param array $criteria
     * @return null|StoredEntityInterface
     * @throws StorageException
     */
    public function retrieve(string $database, array $criteria): ?StoredEntityInterface
    {
        $this->openDatabase($database);

        if (empty($criteria) || !$this->has($database, $criteria)) {
            return null;
        }

        $entries = $this->getEntriesWithCriteria($database, $criteria);
        return $this->prepareEntry(reset($entries));
    }

    /**
     * @param string $database
     * @param array $criteria
     * @return StoredEntityInterface[]
     * @throws StorageException
     */
    public function retrieveAll(string $database, array $criteria = []): array
    {
        $this->openDatabase($database);

        if (empty($criteria)) {
            return $this->prepareEntries(array_values($this->cache[$database]));
        }

        return $this->prepareEntries($this->getEntriesWithCriteria($database, $criteria));
    }

    /**
     * @param string $database
     * @param array $criteria
     * @return bool
     * @throws StorageException
     */
    public function has(string $database, array $criteria): bool
    {
        return !empty($this->getEntriesWithCriteria($database, $criteria));
    }

    /**
     * @param string $database
     * @param array $criteria
     * @return array
     * @throws StorageException
     */
    private function getEntriesWithCriteria(string $database, array $criteria): array
    {
        $this->openDatabase($database);

        $entries = (array) $this->cache[$database];
        $matches = [];

        foreach ($entries as $id => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $matchCount = 0;
            foreach ($criteria as $key => $value) {
                if (array_key_exists($key, $entry) && $entry[$key] === (string) $value) {
                    $matchCount++;
                }
            }

            if ($matchCount === count($criteria)) {
                $matches[$id] = $entry;
            }
        }

        return $matches;
    }

    /**
     * @param array $entry
     * @return StoredEntity
     */
    private function prepareEntry(array $entry): StoredEntity
    {
        $preparedEntry = new StoredEntity($entry);

        if (!empty($entry['id'])) {
            $preparedEntry->setId($entry['id']);
        }

        return $preparedEntry;
    }

    /**
     * @param array $entries
     * @return StoredEntity[]
     */
    private function prepareEntries(array $entries): array
    {
        $prepared = [];

        foreach ($entries as $entry) {
            $prepared[] = $this->prepareEntry($entry);
        }

        return $prepared;
    }

    /**
     * @param string $fileName
     * @return array
     * @throws StorageException
     */
    private function readFile(string $fileName): array
    {
        if (!file_exists($fileName) || !is_readable($fileName)) {
            throw new StorageException('The given file is not readable or does not exist.');
        }

        $jsonData = file_get_contents($fileName);

        if ($jsonData === false) {
            throw new StorageException('The given file could not be read.');
        }

        $data = json_decode($jsonData, true);

        if ($data === null) {
            throw new StorageException('The data in this file is not valid JSON');
        }

        return $data;
    }

    /**
     * @param string $database
     * @return void
     * @throws StorageException
     */
    private function openDatabase(string $database): void
    {
        if (array_key_exists($database, $this->cache)) {
            return;
        }

        $file = $this->baseDirectory . '/' . $database . '.json';

        if (!file_exists($file)) {
            file_put_contents($file, '{}');
        }

        $data = $this->readFile($file);

        $this->cache[$database] = $data;
    }

    /**
     * @param string $database
     * @throws StorageException
     */
    private function syncDatabase(string $database): void
    {
        if (!array_key_exists($database, $this->cache)) {
            throw new StorageException('Cannot sync a database which isn\'t cached.');
        }

        $file = $this->baseDirectory . '/' . $database . '.json';

        if (!file_exists($file)) {
            file_put_contents($file, '{}');
        }

        $cache = $this->cache[$database];
        $data = json_encode($cache);

        $this->writeFile($file, $data);
    }

    /**
     * @param string $fileName
     * @param mixed $data
     * @throws StorageException
     */
    private function writeFile(string $fileName, $data): void
    {
        if (!is_writable($fileName) || file_put_contents($fileName, json_encode($data)) === false) {
            throw new StorageException('Failed to store user data to file; is it writable?');
        }
    }
}