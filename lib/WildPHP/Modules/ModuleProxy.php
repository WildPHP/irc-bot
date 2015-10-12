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

use WildPHP\Traits\EventEmitterTrait;
use WildPHP\Traits\LoopTrait;

class ModuleProxy
{
	use EventEmitterTrait;
	use LoopTrait;

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
	public function loadModules($modules)
	{
		if (empty($modules) || !is_array($modules))
			return;

		foreach ($modules as $module)
		{
			echo 'Attempting to load module ' . $module . '...' . PHP_EOL;

			try
			{
				$object = ModuleFactory::create($module);
			}
			catch (\Exception $e)
			{
				echo 'Module ' . $module . ' could not be loaded due to an error and will be skipped for this run: ' . $e->getMessage() . PHP_EOL;
				continue;
			}

			$object->setEventEmitter($this->getEventEmitter());
			$object->setLoop($this->getLoop());
			$object->setModulePool($this->poolObject);

			echo 'Storing module in module pool with key ' . $object->getShortName() . PHP_EOL;
			$this->poolObject->add($object, $object->getShortName());

			echo 'Module ' . $module . ' loaded.' . PHP_EOL;
		}
	}

	public function initializeModules()
	{
		$modules = $this->poolObject->getAll();

		foreach ($modules as $key => $object)
		{
			try
			{
				if (method_exists($object, 'setup'))
				{
					call_user_func([$object, 'setup']);
					echo 'setup() method exists and was called on module ' . $key . PHP_EOL;
				}
			}
			catch (\Exception $e)
			{
				echo 'Module ' . $key . ' was loaded, but could not be properly initialised. This can cause instability. The message given: ' . $e->getMessage() . PHP_EOL;
				continue;
			}
		}

		$this->getEventEmitter()->emit('wildphp.init.after');
	}
}