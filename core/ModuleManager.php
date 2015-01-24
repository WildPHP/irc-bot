<?php

/**
 * IRC Bot
 *
 * LICENSE: This source file is subject to Creative Commons Attribution
 * 3.0 License that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
 * and use this script commercially/non-commercially. My only requirement is that
 * you keep this header as an attribution to my work. Enjoy!
 *
 * @license http://creativecommons.org/licenses/by/3.0/
 *
 * @package WildPHP
 */
namespace WildPHP\core;

class ModuleManager
{
	private $allModules = array();
	public function __construct()
	{
		// Scan the modules directory for any available modules.
		$files = scandir(WPHP_ROOT . '/modules');
		
		// Loop over them, check if each is a directory.
		foreach ($files as $file)
		{
			if (is_dir(WPHP_ROOT . '/modules/' . $file) && $file != '.' && $file != '..')
				$this->allModules[] = $file;
		}
		
		spl_autoload_register('WildPHP\\core\\ModuleManager::autoLoad');
	}
	
	
	// Load a module. Resolve its dependencies. Recurse over dependencies.
	public function loadModule($module)
	{
		$module = 'WildPHP\\modules\\' . $module;
		$instance = new $module;
	}
	
	// Resolve dependencies for a module.
	private function resolveDependencies($module)
	{
		
	}
	
	// The autoloader for modules.
	static function autoLoad($class)
	{
		$class = str_replace('WildPHP\\modules\\', '', $class);
		require_once(WPHP_ROOT . '/modules/' . $class . '/' . $class . '.php');
	}
}