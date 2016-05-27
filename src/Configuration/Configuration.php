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

class Configuration
{
	/**
	 * @var string
	 */
	const BACKEND = ConfigurationBackends::NEON;

	/**
	 * @var ConfigurationStorage
	 */
	protected static $storage = null;
	
	public static function initialize()
	{
		$backendClass = self::BACKEND;

		if (!class_exists($backendClass))
			throw new \RuntimeException('The configuration backend ' . $backendClass . ' was not found!');

		self::$storage = new ConfigurationStorage($backendClass::getAllEntries());
	}

	/**
	 * @param string $key
	 * @return ConfigurationItem
	 * @throws ConfigurationItemNotFoundException
	 */
	public static function get(string $key)
	{
		return self::$storage->getItem($key);
	}

	/**
	 * @param ConfigurationItem $configurationItem
	 */
	public static function set(ConfigurationItem $configurationItem)
	{
		self::$storage->setItem($configurationItem);
	}
}