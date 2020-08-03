<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage\Providers;

use WildPHP\Core\Storage\StorageException;
use WildPHP\Core\Storage\StoredEntityInterface;

class JsonStorageProvider extends BaseStorageProvider
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

    /**
     * JsonStorageProvider constructor.
     * @param string $baseDirectory
     */
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
        } else {
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
        $entryId = reset($ids);
        unset($cache[$entryId]);

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

        foreach (array_keys($entries) as $entryId) {
            unset($cache[$entryId]);
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
    public function getEntriesWithCriteria(string $database, array $criteria): array
    {
        $this->openDatabase($database);

        $entries = (array)$this->cache[$database];
        return self::matchEntries($entries, $criteria);
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

        try {
            $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new StorageException('Unable to decode JSON data from storage');
        }

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

        $data = $this->cache[$database];

        $this->writeFile($file, $data);
    }

    /**
     * @param string $fileName
     * @param mixed $data
     * @throws StorageException
     */
    private function writeFile(string $fileName, $data): void
    {
        try {
            if (!is_writable($fileName) || file_put_contents(
                    $fileName,
                    json_encode($data, JSON_THROW_ON_ERROR)
                ) === false) {
                throw new StorageException('Failed to store user data to file; is it writable?');
            }
        } catch (\JsonException $e) {
            throw new StorageException('Unable to convert data to be saved to JSON for storage');
        }
    }
}
