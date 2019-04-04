<?php
declare(strict_types=1);

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

use RuntimeException;

class PhpBackend implements ConfigurationBackendInterface
{
    /**
     * @var string
     */
    protected $configFile = '';

    /**
     * @var array
     */
    private $configuration;

    /**
     * NeonBackend constructor.
     *
     * @param string $configFile
     */
    public function __construct(string $configFile)
    {
        if (!file_exists($configFile)) {
            throw new RuntimeException('Could not read configuration file');
        }

        $this->configFile = $configFile;
        $this->configuration = include $configFile;
    }

    /**
     * @return array
     */
    public function getAllEntries(): array
    {
        return $this->configuration;
    }

    /**
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }
}