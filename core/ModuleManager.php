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

class ModuleManager
{
	private $module_dir;
	private $modules = array();
	private $loadedModules = array();
	private $status = array();

	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var object
	 */
	protected $bot;

	public function __construct($bot, $dir = WPHP_MODULE_DIR)
	{
		$this->module_dir = $dir;
		$this->bot = $bot;

		// Register our autoloader.
		spl_autoload_register(array($this, 'autoLoad'));

		// Scan the modules directory for any available modules
		foreach (scandir($this->module_dir) as $file)
		{
			if (is_dir($this->module_dir . $file) && $file != '.' && $file != '..')
			{
				$this->modules[] = $file;
			}
		}
	}

	public function setup()
	{
		if (empty($this->loadedModules))
			$this->loadModules($this->modules);
	}

	public function loadModules($modules)
	{
		$success = true;
		foreach ($modules as $module)
		{
			if (!$this->loadModule($module))
				$success = false;
		}
		return $success;
	}

	// Load a module. Resolve its dependencies. Recurse over dependencies
	public function loadModule($module)
	{
		$module_full = 'WildPHP\\modules\\' . $module;

		if (array_key_exists($module, $this->status) && $this->status[$module] === false)
			return false;

		if ($this->moduleLoaded($module))
			return true;

		$this->bot->log('Loading module ' . $module . '...', 'MODMGR');
		if ($this->moduleAvailable($module) && class_exists($module_full))
		{
			// Need any dependencies?
			$requires = $this->checkDependencies($module);

			// Looks like we have some modules to load before anything else happens.
			if ($requires !== true)
			{
				$this->bot->log('Module ' . $module . ' needs extra dependencies (' . implode(', ', $requires) . '). Queued up until dependencies are satisfied.', 'MODMGR');

				// The function returned a list of modules we need. Load those first.
				if (!$this->loadModules($requires))
				{
					$this->bot->log('Could not satisfy dependencies of module ' . $module . '; module not initialised.', 'MODMGR');
					$this->status[$module] = false;
					return false;
				}
			}

			// Okay, so the class exists.
			$this->loadedModules[$module] = new $module_full($this->bot);
			$this->bot->log('Module ' . $module . ' loaded.', 'MODMGR');
			$this->status[$module] = true;
			return true;
		}
		else
		{
			$this->bot->log('Could not load non-existing module ' . $module . '; module not initialised.', 'MODMGR');
			$this->status[$module] = false;
			return false;
		}
	}

	// Check the dependencies for a module. Note: the $dependencies method MUST be
	public function checkDependencies($module)
	{
		$module_full = 'WildPHP\\modules\\' . $module;

		// It has no dependencies? Good!
		if (!property_exists($module_full, 'dependencies'))
			return true;

		$needs = array();
		foreach ($module_full::$dependencies as $dep)
		{
			if (!$this->moduleLoaded($dep))
				$needs[] = $dep;
		}

		if (empty($needs))
			return true;
		else
			return $needs;
	}

	// Reverse the loading of the module.
	public function unloadModule($module)
	{
		if (!empty($this->modules[$module]))
			unset($this->modules[$module]);
	}

	// The autoloader for modules
	public function autoLoad($class)
	{
		$class = str_replace('WildPHP\\modules\\', '', $class);
		require_once($this->module_dir . $class . '/' . $class . '.php');
	}

	// Is a module loaded?
	public function moduleLoaded($module)
	{
		return array_key_exists($module, $this->loadedModules);
	}

	public function moduleAvailable($module)
	{
		return in_array($module, $this->modules);
	}

	public function getModuleInstance($module)
	{
		if (!$this->moduleLoaded($module))
			return false;

		return $this->loadedModules[$module];
	}
}
