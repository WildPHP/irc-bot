<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;


class MemoryBackend implements ConfigurationBackendInterface
{
    /**
     * @var array
     */
    private $entries = [];

    public function __construct(string $configFile)
    {
    }

    public function getAllEntries(): array
    {
        return $this->entries;
    }
}