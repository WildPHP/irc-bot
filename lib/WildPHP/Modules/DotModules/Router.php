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

namespace WildPHP\Modules\DotModules;

use WildPHP\Modules\ModuleProviders\DirectoryScanner;
use WildPHP\Modules\ModuleProviders\ArrayScanner;

class Router
{
	/**
	 * @param array $parsedModules
	 * @return bool|array false on failure, array with module classes on success
	 */
	public function routeAll($parsedModules)
	{
		if (!is_array($parsedModules) || empty($parsedModules))
			return false;

		$dirScanner = new DirectoryScanner();
		$arrayScanner = new ArrayScanner();

		$buffer = [];
		foreach ($parsedModules as $module)
		{
			if (empty($module['type']) || empty($module['string']))
				continue;

			if ($module['type'] == 'dir')
				$dirScanner->scanDirectory($module['string']);

			elseif ($module['type'] == 'class')
				$buffer[] = $module['string'];
		}

		$arrayScanner->scanArray($buffer);

		$modules = array_unique(array_merge($dirScanner->getValidModules(), $arrayScanner->getValidModules()));
		return $modules;
	}
}