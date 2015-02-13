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

class ModuleManager
{
	/**
	 * The directory the modules are stored in.
	 * @var string
	 */
	private $module_dir;

	/**
	 * The list of available modules.
	 * @var array
	 */
	private $modules = array();

	/**
	 * The list of loaded modules. Stored as 'module' => object.
	 * @var array
	 */
	private $loadedModules = array();

	/**
	 * The running status of modules. Stored as 'module' => boolean.
	 * @var array
	 */
	private $status = array();

	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var \WildPHP\Bot
	 */
	protected $bot;

	/**
	 * Sets up the module manager.
	 * @param object $bot An instance of the bot.
	 * @param string $dir The directory where the modules are in.
	 */
	public function __construct(Bot $bot, $dir = WPHP_MODULE_DIR)
	{
		$this->module_dir = $dir;
		$this->bot = $bot;

		// Register our autoloader.
		spl_autoload_register(array($this, 'autoLoad'));
	}

	/**
	 * Sets up the initial modules.
	 */
	public function setup()
	{
		// Perform the initial load of modules, but only when there are no loaded modules.
		if (empty($this->loadedModules))
		{
			// Scan for modules.
			$this->scanModules();

			$this->loadModules($this->modules);
		}
	}

	/**
	 * Loads an array of modules.
	 * @param array $modules An array containing the names of the modules to load.
	 * @return bool True if all modules were loaded, false if one or more modules failed to load.
	 */
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

	/**
	 * Load a module and its dependencies.
	 * @param string $module The module name.
	 * @return bool True upon success, false upon failure.
	 */
	public function loadModule($module)
	{
		$module_full = 'WildPHP\\modules\\' . $module;

		if (array_key_exists($module, $this->status) && $this->status[$module] === false)
			return false;

		if ($this->moduleLoaded($module))
			return true;

		$this->bot->log('Loading module ' . $module . '...', 'MODMGR');

		// Uh, so this module does not exist. We can't load a module that does not exist...
		if (!$this->moduleAvailable($module) || !class_exists($module_full))
		{
			$this->bot->log('Could not load non-existing module ' . $module . '; module not initialised.', 'MODMGR');
			$this->status[$module] = false;
			return false;
		}

		// Need any dependencies?
		$requires = $this->checkModuleDependencies($module);

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

	/**
	 * Check for dependencies and, if available, return them.
	 * @param string $module The module to check dependencies for.
	 * @return array|bool Array of dependencies or true (if no dependencies) on success, or false upon failure.
	 */
	public function checkModuleDependencies($module)
	{
		$module_full = 'WildPHP\\modules\\' . $module;

		// It has no dependencies? Good!
		if (!method_exists($module_full, 'getDependencies'))
			return true;

		// Get the dependencies.
		$deps = $module_full::getDependencies();

		// Only arrays accepted, sorry.
		if (!is_array($deps))
			return false;

		// So it should have dependencies, but it doesn't... Okay. Skip over.
		elseif (is_array($deps) && empty($deps))
			return true;

		$needs = array();
		foreach ($deps as $dep)
		{
			// If it's not loaded, we need it.
			if (!$this->moduleLoaded($dep))
				$needs[] = $dep;
		}

		// If all dependencies are satisfied, return true. Else, the required dependencies.
		if (empty($needs))
			return true;
		else
			return $needs;
	}

	/**
	 * Reverses the loading of a module.
	 * @param string $module The module name.
	 * @return bool True or false depending on whether the operation succeeded.
	 */
	public function unloadModule($module)
	{
		if (!$this->moduleLoaded($module))
			return false;

		// Remove any instance of the module.
		unset($this->loadedModules[$module]);
		return true;
	}

	/**
	 * Simple autoloader for modules.
	 * @param string $class The class name for modules to load.
	 */
	public function autoLoad($class)
	{
		$class = str_replace('WildPHP\\modules\\', '', $class);

		if (file_exists($this->module_dir . $class . '/' . $class . '.php'))
			require_once($this->module_dir . $class . '/' . $class . '.php');
	}

	/**
	 * Checks if a module is loaded.
	 * @param string $module The module name.
	 * @return bool True or false depending on whether the module is loaded.
	 */
	public function moduleLoaded($module)
	{
		return array_key_exists($module, $this->loadedModules) && is_object($this->loadedModules[$module]);
	}

	/**
	 * Check if a module is available, loaded or not.
	 * @param string $module The module name.
	 * @return bool True or false depending whether the module is registered and available.
	 */
	public function moduleAvailable($module)
	{
		return in_array($module, $this->modules);
	}

	/**
	 * Scan for (new) modules and register them.
	 */
	public function scanModules()
	{
		// Scan the modules directory for any available modules
		foreach (scandir($this->module_dir) as $file)
		{
			if (is_dir($this->module_dir . $file) && $file != '.' && $file != '..' && !$this->moduleAvailable($file))
			{
				$this->bot->log('Module ' . $file . ' registered.', 'MODMGR');
				$this->modules[] = $file;
			}
		}
	}

	/**
	 * Get all available modules.
	 * @return array The available modules.
	 */
	public function getAvailableModules()
	{
		return $this->modules;
	}

	/**
	 * Get all currently loaded modules. Be careful with this.
	 * @return array All currently loaded modules.
	 */
	public function getLoadedModules()
	{
		return $this->loadedModules;
	}

	/**
	 * Returns the loaded instance of the module, for use by other modules.
	 * @param string $module The module name.
	 * @return object|bool The module instance.
	 */
	public function getModuleInstance($module)
	{
		if (!$this->moduleLoaded($module))
			return false;

		return $this->loadedModules[$module];
	}
}
