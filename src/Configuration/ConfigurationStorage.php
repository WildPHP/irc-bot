<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

use ValidationClosures\Types;
use ValidationClosures\Utils;
use Yoshi2889\Collections\Collection;

class ConfigurationStorage extends Collection
{

	/**
	 * Creates a storage for the following array.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		// Accept any type, except objects.
		parent::__construct(Utils::invert(Types::object()), $config);
	}

	/**
	 * @param string $key
	 *
	 * @return ConfigurationItem
	 * @throws ConfigurationItemNotFoundException
	 */
	public function getItem(string $key): ConfigurationItem
	{
		trigger_error('Configuration can now be handled like a Collection.', E_USER_DEPRECATED);
		$pieces = explode('.', $key);

		$lastPiece = $this->getArrayCopy();
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
		trigger_error('Configuration can now be handled like a Collection.', E_USER_DEPRECATED);
		$key = $configurationItem->getKey();
		$value = $configurationItem->getValue();
		$pieces = explode('.', $key);

		$array = $this->getArrayCopy();
		$lastPiece = &$array;
		foreach ($pieces as $piece)
		{
			$lastPiece = &$lastPiece[$piece];
		}
		$lastPiece = $value;
		$this->exchangeArray($array);
	}
}