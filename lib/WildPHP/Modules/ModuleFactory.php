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

namespace WildPHP\Modules;

use WildPHP\BaseModule;

class ModuleFactory
{
	/**
	 * @param string $moduleClass
	 *
	 * @return BaseModule
	 */
	public static function create($moduleClass)
	{
		if (!class_exists($moduleClass))
			throw new \RuntimeException('ModuleFactory: Unable to create a module from a class which does not exist.');

		try
		{
			$object = new $moduleClass();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException('ModuleFactory: Unable to build module ' . $moduleClass . ': ' . $e->getMessage());
		}

		if (!$object instanceof BaseModule)
			throw new \RuntimeException('ModuleFactory: Attempted to build a non-module.');

		return $object;
	}
}