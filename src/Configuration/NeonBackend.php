<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

use Nette\Neon\Neon;

class NeonBackend implements ConfigurationBackendInterface
{
    /**
     * @var string
     */
    protected $configFile = '';

    /**
     * NeonBackend constructor.
     *
     * @param string $configFile
     */
    public function __construct(string $configFile)
    {
        $this->setConfigFile($configFile);
    }

    /**
     * @return array
     */
    public function getAllEntries(): array
    {
        $data = $this->readNeonFile($this->getConfigFile());
        $decodedData = $this->parseNeonData($data);

        return $decodedData;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    protected function parseNeonData(string $data): array
    {
        $decodedData = Neon::decode($data);

        if (empty($decodedData)) {
            return [];
        }

        return $decodedData;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function readNeonFile(string $file): string
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \RuntimeException('NeonBackend: Cannot read NEON file ' . $file);
        }

        $data = file_get_contents($file);

        if ($data === false) {
            throw new \RuntimeException('NeonBackend: Failed to read NEON file ' . $file);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    /**
     * @param string $configFile
     */
    public function setConfigFile(string $configFile)
    {
        $this->configFile = $configFile;
    }
}