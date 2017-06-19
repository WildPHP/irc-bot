<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

class ConfigurationStorage
{
	/**
	 * @var array
	 */
	private $config = [];

	/**
	 * Creates a storage for the following array.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @param string $key
	 *
	 * @return ConfigurationItem
	 * @throws ConfigurationItemNotFoundException
	 */
	public function getItem(string $key): ConfigurationItem
	{
		$pieces = explode('.', $key);

		$lastPiece = $this->config;
		foreach ($pieces as $piece)
		{
			if (empty($lastPiece))
				throw new ConfigurationItemNotFoundException();

			if (array_key_exists($piece, $lastPiece))
				$lastPiece = $lastPiece[$piece];
			else
				throw new ConfigurationItemNotFoundException();
		}

		$configurationItem = new ConfigurationItem($key, $lastPiece);

		return $configurationItem;
	}

	/**
	 * @param ConfigurationItem $configurationItem
	 */
	public function setItem(ConfigurationItem $configurationItem)
	{
		$key = $configurationItem->getKey();
		$value = $configurationItem->getValue();
		$pieces = explode('.', $key);

		$lastPiece = &$this->config;
		foreach ($pieces as $piece)
		{
			$lastPiece = &$lastPiece[$piece];
		}
		$lastPiece = $value;
	}

	/**
	 * @return array
	 */
	public function getAllEntries(): array
	{
		return $this->config;
	}
}