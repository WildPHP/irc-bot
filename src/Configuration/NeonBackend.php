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

use Nette\Neon\Neon;
use WildPHP\Core\Logger\Logger;

class NeonBackend implements ConfigurationBackendInterface
{
	const CONFIG_FILE = WPHP_ROOT_DIR . 'config.neon';

	public static function getAllEntries(): array
	{
		$data = self::readNeonFile(self::CONFIG_FILE);
		$decodedData = self::parseNeonData($data);
		return $decodedData;
	}

	protected static function parseNeonData(string $data): array
	{
		$decodedData = Neon::decode($data);

		if (empty($decodedData))
			return [];
		
		return $decodedData;
	}

	protected static function readNeonFile(string $file): string
	{
		Logger::info('Reading config file', ['file' => $file]);
		if (!file_exists($file) || !is_readable($file))
			throw new \RuntimeException('NeonBackend: Cannot read NEON file ' . $file);

		$data = file_get_contents($file);

		if ($data === false)
			throw new \RuntimeException('NeonBackend: Failed to read NEON file ' . $file);

		return $data;
	}
}