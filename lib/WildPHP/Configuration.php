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

namespace WildPHP;

use Nette\Neon;
use RuntimeException;

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
	 * @param Bot $bot
	 * @throws \Exception on read error.
	 */
	public function __construct($bot, $config)
	{
		try
		{
			// Open the file and surpress errors; we'll do our own error handling here.
			$data = @file_get_contents($config);
			if(!empty($data) && is_string($data))
				$this->config = Neon\Neon::decode(file_get_contents($config));
			else
				throw new ConfigurationException('The configuration could not be loaded. Please check the file ' . $config . ' exists and is readable/not corrupt.');
		}
		catch(Neon\Exception $e)
		{
			throw new ConfigurationException('Configuration syntax error: ' . $e->getMessage());
		}

		$this->bot = $bot;
	}

	/**
	 * Returns an item stored in the configuration.
	 * @param string $key The key of the configuration item to get.
	 * @return false|mixed False on failure; mixed on success.
	 */
	public function get($key)
	{
		$pieces = explode('.', $key);

		$lastPiece = $this->config;
		foreach($pieces as $piece)
		{
			if(array_key_exists($piece, $lastPiece))
				$lastPiece = $lastPiece[$piece];
			else
				return false;
		}

		return $lastPiece;
	}
}

class ConfigurationException extends RuntimeException
{

}
