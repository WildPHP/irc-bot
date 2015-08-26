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

use WildPHP\LogManager\LogLevels;

class ModuleManager extends Manager
{
	/**
	 * The directory the modules are stored in.
	 * @var string
	 */
	private $moduleDir;

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
	 * Sets up the module manager.
	 * @param Bot $bot An instance of the bot.
	 * @param string $dir The directory where the modules are in.
	 */
	public function __construct(Bot $bot, $dir = WPHP_MODULE_DIR)
	{
		parent::__construct($bot);

		$this->moduleDir = $dir;
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
			$this->scan();
			$this->loadMultiple($this->modules);
		}
	}

	/**
	 * Loads an array of modules.
	 * @param array $modules An array containing the names of the modules to load.
	 * @return bool True if all modules were loaded, false if one or more modules failed to load.
	 */
	public function loadMultiple(array $modules)
	{
		$success = true;
		foreach ($modules as $module)
		{
			if (!$this->load($module))
				$success = false;
		}
		return $success;
	}

	/**
	 * Load a module and its dependencies.
	 * @param string $module The module name.
	 * @return bool True upon success.
	 * @throws UnableToLoadModuleException when a module could not be loaded.
	 */
	public function load($module)
	{
		$module_full = 'WildPHP\\Modules\\' . $module;

		// We already failed to load this module.
		if ($this->getStatus($module) === false)
			throw new UnableToLoadModuleException('The Module Manager was unable to load module ' . $module);

		if ($this->isLoaded($module))
			return true;

		$this->log('Loading module {module}...', array('module' => $module), LogLevels::DEBUG);

		// Uh, so this module does not exist. We can't load a module that does not exist...
		if (!$this->isAvailable($module) || !class_exists($module_full))
			throw new UnableToLoadModuleException('The Module Manager was unable to load module ' . $module);

		// Okay, so the class exists.
		$this->loadedModules[$module] = new $module_full($this->getBot());
		$this->log('Module {module} loaded and initialised.', array('module' => $module), LogLevels::INFO);
		return $this->setStatus($module, true);
	}

	/**
	 * Sets module status. Always returns the status you set.
	 * @param string $module The module to set status for.
	 * @param boolean $status The status to set. Default is false.
	 * @return boolean Always returns $status, or false on failure.
	 */
	public function setStatus($module, $status = false)
	{
		// Do not check if the module exists.
		if ($status !== true && $status !== false)
			return false;

		$this->status[$module] = $status;
		return $status;
	}

	/**
	 * Gets the status for a module. Returns null if it does not exist.
	 * @param string $module
	 * @return boolean|null
	 */
	public function getStatus($module)
	{
		if (!array_key_exists($module, $this->status))
			return null;

		return $this->status[$module];
	}

	/**
	 * Reverses the loading of a module.
	 * @param string $module The module name.
	 * @return bool True or false depending on whether the operation succeeded.
	 */
	public function unload($module)
	{
		if (!$this->isLoaded($module))
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
		$class = str_replace('WildPHP\\Modules\\', '', $class);

		if (file_exists($this->moduleDir . $class . '/' . $class . '.php'))
			require_once($this->moduleDir . $class . '/' . $class . '.php');
	}

	/**
	 * Checks if a module is loaded.
	 * @param string $module The module name.
	 * @return bool True or false depending on whether the module is loaded.
	 */
	public function isLoaded($module)
	{
		return $this->getStatus($module) && array_key_exists($module, $this->loadedModules) && is_object($this->loadedModules[$module]);
	}

	/**
	 * Check if a module is available, loaded or not.
	 * @param string $module The module name.
	 * @return bool True or false depending whether the module is registered and available.
	 */
	public function isAvailable($module)
	{
		return in_array($module, $this->modules);
	}

	/**
	 * Scan for (new) modules and register them.
	 */
	public function scan()
	{
		// Scan the modules directory for any available modules
		foreach (scandir($this->moduleDir) as $file)
		{
			if (is_dir($this->moduleDir . $file) && $file != '.' && $file != '..' && !$this->isAvailable($file))
			{
				$this->log('Module {module} found and registered.', array('module' => $file), LogLevels::DEBUG);
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
	 * @return BaseModule The module instance.
	 */
	public function getModuleInstance($module)
	{
		if (!$this->isAvailable($module))
			throw new \InvalidArgumentException('Module ' . $module . ' does not exist.');

		// Try to load it.
		if (!$this->isLoaded($module))
			$this->load($module);

		return $this->loadedModules[$module];
	}
}

class UnableToLoadModuleException extends \RuntimeException
{
}