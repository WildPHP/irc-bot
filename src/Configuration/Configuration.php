<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Configuration implements ComponentInterface
{
	use ComponentTrait;

	/**
	 * @var ConfigurationBackendInterface
	 */
	protected $backend = null;

	/**
	 * @var ConfigurationStorage
	 */
	protected $storage = null;

	/**
	 * Configuration constructor.
	 *
	 * @param ConfigurationBackendInterface $configurationBackend
	 */
	public function __construct(ConfigurationBackendInterface $configurationBackend)
	{
		$this->setBackend($configurationBackend);

		$this->setStorage(new ConfigurationStorage($configurationBackend->getAllEntries()));
	}

	/**
	 * @param string $key
	 *
	 * @return ConfigurationItem
	 * @throws ConfigurationItemNotFoundException
	 */
	public function get(string $key)
	{
		return $this->getStorage()
			->getItem($key);
	}

	/**
	 * @param ConfigurationItem $configurationItem
	 */
	public function set(ConfigurationItem $configurationItem)
	{
		$this->getStorage()
			->setItem($configurationItem);
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

	/**
	 * @return ConfigurationStorage
	 */
	public function getStorage(): ConfigurationStorage
	{
		return $this->storage;
	}

	/**
	 * @param ConfigurationStorage $storage
	 */
	public function setStorage(ConfigurationStorage $storage)
	{
		$this->storage = $storage;
	}
}