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

use WildPHP\Traits\LoggerTrait;

class ModuleProxy
{
	use LoggerTrait;

	/**
	 * @var ModulePool
	 */
	private $poolObject;

	/**
	 * Initialize our module pool.
	 */
	public function __construct()
	{
		$this->poolObject = new ModulePool();
	}

	/**
	 * @param string $module
	 */
	public function loadModule($module)
	{
		if (!class_exists($module))
			return;

		$this->loadModules(array($module));
	}

	/**
	 * @param string[] $modules
	 */
	public function loadModules(array $modules)
	{
		foreach ($modules as $module)
		{
			$this->getLogger()->debug('Attempting to load module ' . $module . '...');

			try
			{
				$object = ModuleFactory::create($module);
			}
			catch (\Exception $e)
			{
				$this->getLogger()->warning('Module ' . $module . ' could not be loaded due to an error and will be skipped for this run: ' . $e);
				continue;
			}
			$this->getLogger()->debug('Storing module in module pool with key ' . $object->getShortName());
			$this->poolObject->add($object, $object->getShortName());

			$this->getLogger()->info('Module ' . $module . ' loaded and initialized.');
		}
	}
}