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

class Autoloader
{
	static function load($class)
	{
		$fixes = array(
				'WildPHP' => '.',
				'Core' => 'core',
		);

		// Split $class to the "path" and "classname" parts
		$class = explode('\\', $class);
		$classpath = $class;
		array_pop($classpath);
		$classname = end($class) . '.php';

		// Apply fixes to path
		$classpath = str_replace(array_keys($fixes), array_values($fixes), $classpath);

		// Assemble path
		$classpath = implode('/', $classpath) . '/';

		$path = array(
			WPHP_ROOT_DIR . $classpath . $classname,			// Try to load the class from project root
			WPHP_ROOT_DIR . 'lib/' . $classpath . $classname	// Check for files in lib/classpath/classname.php
		);

		foreach ($path as $p) {
			if(file_exists($p))
			{
				echo '[AUTOLOAD] Loaded "' . $p . '"' . PHP_EOL;
				require $p;
				return true;
			}
		}
		
		return false;
	}
}
