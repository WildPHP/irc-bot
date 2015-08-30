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

use WildPHP\Event\NewCommandEvent;
use WildPHP\Event\NewListenerEvent;
use WildPHP\LogManager\LogLevels;

class ModuleManager extends Manager
{
	/**
	 * The directory the modules are stored in.
	 *
	 * @var string
	 */
	private $moduleDir;

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
	 * Commands registered per module. Stored as 'module' => array('command', 'command', ...)
	 *
	 * @var array
	 */
	private $registeredCommands = [];

	/**
	 * Listeners registered per module. Stored as 'module' => array('listener' => array(callback, ...))
	 *
	 * @var array
	 */
	private $registeredListeners = [];

	/**
	 * Sets up the module manager.
	 *
	 * @param Bot    $bot An instance of the bot.
	 * @param string $dir The directory where the modules are in.
	 */
	public function __construct(Bot $bot, $dir = WPHP_MODULE_DIR)
	{
		parent::__construct($bot);

		$this->moduleDir = $dir;
		spl_autoload_register([$this, 'autoLoad']);

		// Register ourself to events.
		$this->getEventManager()->getEvent('NewListener')->registerListener([$this, 'catchListener']);
		$this->getEventManager()->getEvent('NewCommand')->registerListener([$this, 'catchCommand']);
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

		$this->log('Loading module {module}...', ['module' => $module], LogLevels::DEBUG);

		// Uh, so this module does not exist. We can't load a module that does not exist...
		if (!$this->isAvailable($module) || !class_exists($module_full))
			throw new UnableToLoadModuleException('The Module Manager was unable to load module ' . $module);

		// Okay, so the class exists.
		try
		{
			$this->loadedModules[$module] = new $module_full($this->getBot());
			$this->loadedModules[$module]->init();
		}

			// Kick any module that failed off the stack.
		catch (\Exception $e)
		{
			$this->log('Kicking module {module} off the stack because an exception was triggered during initialization: ' . $e->getMessage() . '. You might want to fix this.');
			$this->kick($module);
		}
		$this->log('Module {module} loaded and initialised.', ['module' => $module], LogLevels::INFO);
		return $this->setStatus($module, true);
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

		// Clean up their mess...
		if (array_key_exists($module, $this->registeredCommands))
		{
			foreach ($this->registeredCommands[$module] as $command)
			{
				$this->getEventManager()->getEvent('BotCommand')->removeCommand($command);
			}
		}

		// And more mess..
		if (array_key_exists($module, $this->registeredListeners))
		{
			foreach ($this->registeredListeners[$module] as $event => $listeners)
			{
				$event = $this->getEventManager()->getEvent($event);
				foreach ($listeners as $listener)
				{
					$event->removeListener($listener);
				}
			}
		}

		unset($this->modules[array_search($module, $this->modules)]);
		$this->log('Module ' . $module . ' kicked from stack and cleaned up.');
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
	 * Catches listeners that have been added.
	 *
	 * @param NewListenerEvent $e
	 */
	public function catchListener(NewListenerEvent $e)
	{
		if (is_null($e->getModule()))
			return;

		$module = $e->getModule();

		// Find the module name.
		if (!in_array($module, $this->loadedModules))
			return;

		$name = array_search($module, $this->loadedModules);
		$ename = $this->getEventManager()->findNameByObject($e->getEvent());

		$this->log('Listener caught for event ' . $ename . ' registered by module ' . $name);

		// And note the listener.
		$this->registeredListeners[$name][$ename][] = $e->getCall();
	}

	/**
	 * Catches commands that have been added.
	 *
	 * @param NewCommandEvent $e
	 */
	public function catchCommand(NewCommandEvent $e)
	{
		if (is_null($e->getModule()))
			return;

		$module = $e->getModule();

		// Find the module name.
		if (!in_array($module, $this->loadedModules))
			return;

		$name = array_search($module, $this->loadedModules);

		$this->log('Command ' . $e->getCommand() . ' registered by module ' . $name);

		// And note the listener.
		$this->registeredCommands[$name][] = $e->getCommand();
	}

	/**
	 * Simple autoloader for modules.
	 *
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
	 * Scan for (new) modules and register them.
	 */
	public function scan()
	{
		// Scan the modules directory for any available modules
		foreach (scandir($this->moduleDir) as $file)
		{
			if (is_dir($this->moduleDir . $file) && $file != '.' && $file != '..' && !$this->isAvailable($file))
			{
				$this->log('Module {module} found and registered.', ['module' => $file], LogLevels::DEBUG);
				$this->modules[] = $file;
			}
		}
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
	 * @return array All currently loaded modules.
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