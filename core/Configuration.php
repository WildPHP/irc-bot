<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

namespace WildPHP\Core;

class Configuration
{
	private $config = array();

	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var object
	 */
	protected $bot;

	/**
	 * Loads the config file and parses it.
	 * @param string $config The path to the config file.
	 */
	public function __construct($bot, $config)
	{
		try {
			// Open the file and surpress errors; we'll do our own error handling here.
			$data = @file_get_contents($config);
			if (!empty($data) && is_string($data))
				$this->config = \Nette\Neon\Neon::decode(file_get_contents($config));
			else
				die('The configuration could not be loaded. Please check the file ' . $config . ' exists and is readable/not corrupt.' . PHP_EOL);
		} catch (Nette\Neon\Exception $e) {
			die('Configuration syntax error: ' . $e->getMessage() . PHP_EOL);
		}

		$this->bot = $bot;
	}

	/**
	 * Returns an item stored in the configuration.
	 * @param string $key The key of the configuration item to get.
	 */
	public function get($key)
	{
		$pieces = explode('.', $key);

		// We can only return something that exists.
		if (array_key_exists($key, $this->config))
			return $this->config[$key];

		// All else fails. No working around that; it doesn't exist. DEAL WITH IT :D
		else
			return false;
	}

	/**
	 * Updates/Creates an item stored in the configuration.
	 * @param string $key   The key of the configuration item to update.
	 * @param string $value The value to update it to.
	 */
	public function set($key, $value)
	{
		$this->config[$key] = $value;
	}
}
