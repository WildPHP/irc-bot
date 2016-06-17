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

namespace WildPHP\Core\DataStorage;


use Flintstone\Flintstone;

class DataStorageFactory
{
	/**
	 * @var string
	 */
	const STORAGE_DIR = WPHP_ROOT_DIR . '/storage/';

	/**
	 * @var array
	 */
	protected static $openStorages = [];

	/**
	 * @param string $storage
	 * @param array $options
	 * @return DataStorage
	 */
	public static function getStorage(string $storage, array $options = []): DataStorage
	{
		if (array_key_exists($storage, self::$openStorages))
			return self::$openStorages[$storage];

		$options = array_merge($options, ['dir' => self::STORAGE_DIR]);

		$flintstone = new Flintstone($storage, $options);
		$dataStorage = new DataStorage($flintstone);
		self::$openStorages[$storage] = $dataStorage;
		return $dataStorage;
	}
}