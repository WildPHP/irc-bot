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

namespace WildPHP\Modules\ModuleProviders;

abstract class BaseScanner
{
	/**
	 * @var string[]
	 */
	protected $validModules = [];

	/**
	 * @return string[]
	 */
	public function getValidModules()
	{
		return $this->validModules;
	}

	/**
	 * @param string $className
	 */
	protected function tryAddValidModule($className)
	{
		if (self::isValidModule($className) && !$this->validModuleExists($className))
			$this->validModules[] = $className;

	}

	/**
	 * @param string $className
	 *
	 * @return boolean
	 */
	public static function isValidModule($className)
	{
		if (!class_exists($className))
			throw new \RuntimeException('Unable to locate class ' . $className);

		$reflection = self::getReflection($className);

		return $reflection->isSubclassOf('\WildPHP\BaseModule');
	}

	/**
	 * @param $className
	 *
	 * @return \ReflectionClass
	 */
	public static function getReflection($className)
	{
		if (!class_exists($className))
			throw new \RuntimeException('Unable to locate class ' . $className);

		return new \ReflectionClass($className);
	}

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	protected function validModuleExists($className)
	{
		return in_array($className, $this->validModules);
	}
}