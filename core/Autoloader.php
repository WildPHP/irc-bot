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
			'WildPHP\\' => '',
			'\\' => '/',
		);
		
		// We'll be checking for the last bit of the class string.
		$class = str_replace(array_keys($fixes), array_values($fixes), $class);
		
		// Check for lib/Class.php...
		if (file_exists(WPHP_ROOT_DIR . '/' . $class . '.php'))
			require_once(WPHP_ROOT_DIR . '/' . $class . '.php');
			
		// lib/Class/Class.php maybe?
		elseif (file_exists(WPHP_ROOT_DIR . '/lib/' . $class . '.php'))
			require_once(WPHP_ROOT_DIR . '/lib/' . $class . '.php');
	}
}
