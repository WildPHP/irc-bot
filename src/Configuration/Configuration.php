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
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Configuration extends Collection implements ComponentInterface
{
	use ComponentTrait;

	/**
	 * @var ConfigurationBackendInterface
	 */
	protected $backend = null;

	/**
	 * Configuration constructor.
	 *
	 * @param ConfigurationBackendInterface $configurationBackend
	 */
	public function __construct(ConfigurationBackendInterface $configurationBackend)
	{
		$this->setBackend($configurationBackend);

		// Accept any type, except objects.
		parent::__construct(Utils::invert(Types::object()), $configurationBackend->getAllEntries());
	}

	/**
	 * @param string $key
	 *
	 * @return ConfigurationItem
	 * @throws ConfigurationItemNotFoundException
	 */
	public function get(string $key)
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
	public function set(ConfigurationItem $configurationItem)
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

	/**
	 * @return ConfigurationBackendInterface
	 */
	public function getBackend(): ConfigurationBackendInterface
	{
		return $this->backend;
	}

	/**
	 * @param ConfigurationBackendInterface $backend
	 */
	public function setBackend(ConfigurationBackendInterface $backend)
	{
		$this->backend = $backend;
	}
}