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

use WildPHP\Traits\EventEmitterTrait;
use WildPHP\Traits\LoopTrait;
use WildPHP\Traits\ModulePoolTrait;

abstract class BaseModule implements BaseModuleInterface
{
	use EventEmitterTrait;
	use LoopTrait;
	use ModulePoolTrait;

	public function checkModuleAvailability($module, $class = '')
	{
		if (!$this->getModulePool()->existsByKey($module))
			return false;

		elseif (!empty($class) && $this->getModulePool()->isInstance($module, $class))
			return false;

		return true;
	}

	public function getWorkingDir()
	{
		return dirname(__FILE__);
	}

	public function getFullyQualifiedName()
	{
		return get_class();
	}

	public function getShortName()
	{
		$reflectionClass = new \ReflectionClass($this);

		return $reflectionClass->getShortName();
	}

	public function getModule($key)
	{
		return $this->getModulePool()->get($key);
	}
}