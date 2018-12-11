<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers;

use PDO;
use WildPHP\Core\Storage\StorageException;

class SQLiteDatabaseStorageProvider extends GenericPdoDatabaseStorageProvider
{
    /**
     * SQLiteStorageProvider constructor.
     * @param string $databaseFile
     * @throws StorageException
     */
    public function __construct(string $databaseFile)
    {
        if (!file_exists($databaseFile)) {
            throw new StorageException('The given database file does not exist');
        }

        $this->pdo = new \PDO('sqlite:' . $databaseFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}