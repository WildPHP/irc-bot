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

class ModuleEmitter
{
	/**
	 * The Api class.
	 *
	 * @var Api
	 */
	private $api;

	/**
	 * The list of available modules.
	 *
	 * @var array
	 */
	private $modules = [];

	/**
	 * The list of loaded modules. Stored as 'module' => object.
	 *
	 * @var BaseModule[]
	 */
	private $loadedModules = [];

	/**
	 * The running status of modules. Stored as 'module' => boolean.
	 *
	 * @var array
	 */
	private $status = [];

	/**
	 * Sets up the module manager.
	 *
	 * @param Api $api An instance of the api.
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
		$modules = $this->api->getConfigurationStorage()->get('modules');
		$this->loadMultiple($modules);
	}

	/**
	 * Loads an array of modules.
	 *
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
	 *
	 * @param string $class The module class.
	 * @return bool True upon success.
	 * @throws UnableToLoadModuleException when a module could not be loaded.
	 */
	public function load($class)
	{
		if ($this->getStatus($class) === false)
			throw new UnableToLoadModuleException('The Module Manager was unable to load module ' . $class);

		if ($this->isLoaded($class))
			return true;

		$this->api->getLogger()->debug('Loading module {module}...', ['module' => $class]);

		// Uh, so this module does not exist. We can't load a module that does not exist...
		if (!class_exists($class))
			throw new UnableToLoadModuleException('The Module Manager was unable to load module ' . $class);

		// Okay, so the class exists.
		try
		{
			$this->loadedModules[$class] = new $class($this->api);
			$this->loadedModules[$class]->init();
		}

			// Kick any module that failed off the stack.
		catch (\Exception $e)
		{
			$this->api->getLogger()->warning('Kicking module {module} off the stack because an exception was triggered during initialization: {exception}. You might want to fix this.',
				array(
					'module' => $class,
					'exception' => $e->getMessage()
				));
			$this->kick($class);
		}
		$this->api->getLogger()->info('Module {module} loaded and initialised.', ['module' => $class]);
		return $this->setStatus($class, true);
	}

	/**
	 * Sets module status. Always returns the status you set.
	 *
	 * @param string  $module The module to set status for.
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
	 *
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
	 *
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
	 * Kicks a module from the stack, making it seem as if the module was never there in the first place.
	 *
	 * @param string $module The module name.
	 */
	public function kick($module)
	{
		if (!$this->isAvailable($module))
			throw new \InvalidArgumentException('Cannot kick non-existing module ' . $module);

		if ($this->isLoaded($module))
			$this->unload($module);

		if (array_key_exists($module, $this->status))
			unset($this->status[$module]);
	}

	/**
	 * Kicks a module by object.
	 *
	 * @param BaseModule $module the module to kick.
	 */
	public function kickByObject(BaseModule $module)
	{
		if (!in_array($module, $this->loadedModules))
			throw new \InvalidArgumentException('Cannot kick a module that is not loaded.');

		$this->kick(array_search($module, $this->loadedModules));
	}

	/**
	 * Checks if a module is loaded.
	 *
	 * @param string $module The module name.
	 * @return bool True or false depending on whether the module is loaded.
	 */
	public function isLoaded($module)
	{
		return $this->getStatus($module) && array_key_exists($module, $this->loadedModules) && is_object($this->loadedModules[$module]);
	}

	/**
	 * Check if a module is available, loaded or not.
	 *
	 * @param string $module The module name.
	 * @return bool True or false depending whether the module is registered and available.
	 */
	public function isAvailable($module)
	{
		return in_array($module, $this->modules);
	}

	/**
	 * Get all available modules.
	 *
	 * @return array The available modules.
	 */
	public function getAvailableModules()
	{
		return $this->modules;
	}

	/**
	 * Get all currently loaded modules. Be careful with this.
	 *
	 * @return BaseModule[] All currently loaded modules.
	 */
	public function getLoadedModules()
	{
		return $this->loadedModules;
	}

	/**
	 * Returns the loaded instance of the module, for use by other modules.
	 *
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