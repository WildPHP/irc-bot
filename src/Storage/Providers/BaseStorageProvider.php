<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage\Providers;

use WildPHP\Core\Storage\StoredEntity;

abstract class BaseStorageProvider implements StorageProviderInterface
{
    /**
     * @param array $entries
     * @param array $criteria
     * @return array
     */
    protected static function matchEntries(array $entries, array $criteria): array
    {
        $matches = [];

        foreach ($entries as $id => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $matchCount = 0;
            foreach ($criteria as $key => $value) {
                if (array_key_exists($key, $entry) && $entry[$key] === $value) {
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
    protected function prepareEntry(array $entry): StoredEntity
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
    protected function prepareEntries(array $entries): array
    {
        $prepared = [];

        foreach ($entries as $entry) {
            $prepared[] = $this->prepareEntry($entry);
        }

        return $prepared;
    }
}
