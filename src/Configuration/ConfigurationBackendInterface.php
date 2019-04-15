<?php
declare(strict_types=1);

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

interface ConfigurationBackendInterface
{

    /**
     * ConfigurationBackendInterface constructor.
     *
     * @param string $configFile
     */
    public function __construct(string $configFile);

    /**
     * @return array
     */
    public function getAllEntries(): array;
}
