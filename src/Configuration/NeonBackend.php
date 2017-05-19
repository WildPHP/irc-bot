<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

		if (empty($decodedData))
			return [];

		return $decodedData;
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	protected function readNeonFile(string $file): string
	{
		if (!file_exists($file) || !is_readable($file))
			throw new \RuntimeException('NeonBackend: Cannot read NEON file ' . $file);

		$data = file_get_contents($file);

		if ($data === false)
			throw new \RuntimeException('NeonBackend: Failed to read NEON file ' . $file);

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