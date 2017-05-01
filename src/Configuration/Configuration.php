<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Configuration;

use WildPHP\Core\ComponentTrait;

class Configuration
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
		return $this->getStorage()->getItem($key);
	}

	/**
	 * @param ConfigurationItem $configurationItem
	 */
	public function set(ConfigurationItem $configurationItem)
	{
		$this->getStorage()->setItem($configurationItem);
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