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

namespace WildPHP\CoreModules\Configuration;

use WildPHP\BaseModule;
use Nette\Neon\Neon;

class Configuration extends BaseModule
{
	/**
	 * @var ConfigurationStorage
	 */
	protected $storage = null;

	public function setup()
	{
		try
		{
			// Open the file and surpress errors; we'll do our own error handling here.
			$data = @file_get_contents(WPHP_ROOT_DIR . '/config.neon');

			if (!empty($data) && is_string($data))
				$decoded = Neon::decode($data);
			else
				throw new ConfigurationException('The configuration could not be loaded. Please check the file config.neon file exists and is readable/not corrupt.');
		}
		catch (\Exception $e)
		{
			throw new ConfigurationException('Configuration syntax error: ' . $e->getMessage());
		}

		$this->storage = new ConfigurationStorage($decoded);
	}

	/**
	 * @param string $key
	 *
	 * @return false|mixed
	 */
	public function get($key)
	{
		return $this->storage->get($key);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		$this->storage->set($key, $value);
	}
}