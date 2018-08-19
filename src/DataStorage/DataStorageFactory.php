<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\DataStorage;

use Flintstone\Formatter\JsonFormatter;

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
	 *
	 * @return DataStorage
	 */
	public static function getStorage(string $storage, array $options = []): DataStorage
	{
		if (array_key_exists($storage, self::$openStorages))
			return self::$openStorages[$storage];

		$options = array_merge($options, [
			'formatter' => new JsonFormatter(),
			'dir' => self::STORAGE_DIR
		]);

		$dataStorage = new DataStorage($storage, $options);
		self::$openStorages[$storage] = $dataStorage;

		return $dataStorage;
	}
}