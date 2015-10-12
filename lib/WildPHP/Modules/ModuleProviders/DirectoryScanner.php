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

namespace WildPHP\Modules\ModuleProviders;

class DirectoryScanner extends BaseScanner
{
	/**
	 * @param string $dirName
	 */
	public function __construct($dirName)
	{
		if (!empty($dirName))
			$this->scanDirectory($dirName);
	}

	public function scanDirectory($dir)
	{
		if (!is_dir($dir) || !is_readable($dir))
			throw new \RuntimeException('DirectoryScanner: ' . $dir . ' is not a directory.');

		$entries = scandir($dir);

		foreach ($entries as $entry)
		{
			if ($entry == '.' || $entry == '..' || !is_dir($dir . '/' . $entry))
				continue;

			if (file_exists($dir . '/' . $entry . '/' . $entry . '.php'))
			{
				$file = $dir . '/' . $entry . '/' . $entry . '.php';

				$namespace = $this->determineNamespace($file);
				$class = $this->determineClassName($file);
				if (!$namespace || !$class)
					continue;

				// Include the file so the class is available and our autoloader doesn't try to
				// load files that aren't there.
				include_once($file);

				$className = $namespace . '\\' . $class;

				echo 'Determined possible module: ' . $className . ' (is available: ' . (class_exists($className) ? 'Yes' : 'No') . ', is valid: ' . (self::isValidModule($className) ? 'Yes' : 'No') . ')' . PHP_EOL;

				// Et voilla.
				$this->tryAddValidModule($className);
			}
		}
	}

	public function determineNamespace($file)
	{
		if (!file_exists($file) || !is_readable($file))
			return false;

		$contents = file_get_contents($file);

		$result = preg_match('/namespace\s+([a-zA-Z0-9\\\_]+)(?:\s+)?;/', $contents, $matches);

		if ($result == false)
			return false;

		if (substr($matches[1], 0, 1) !== '\\')
			$matches[1] = '\\' . $matches[1];

		return $matches[1];
	}

	public function determineClassName($file)
	{
		if (!file_exists($file) || !is_readable($file))
			return false;

		$contents = file_get_contents($file);

		$result = preg_match('/class\s+([a-zA-Z0-9_]+)/', $contents, $matches);

		if ($result == false)
			return false;

		return $matches[1];
	}


}