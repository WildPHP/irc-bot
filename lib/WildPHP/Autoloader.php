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

class Autoloader
{
	static function load($class)
	{

		// Split $class to the "path" and "classname" parts
		$class = explode('\\', $class);
		$classpath = $class;
		array_pop($classpath);
		$classname = end($class) . '.php';

		// Assemble path
		$classpath = implode('/', $classpath) . '/';

		$path = WPHP_LIB_DIR . $classpath . $classname;	// Check for files in lib/classpath/classname.php

		if(file_exists($path))
		{
			echo '[AUTOLOAD] Loaded "' . $path . '"' . PHP_EOL;
			require $path;
			return true;
		}

		return false;
	}
}
