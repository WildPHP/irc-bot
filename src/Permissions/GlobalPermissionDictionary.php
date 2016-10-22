<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

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

namespace WildPHP\Core\Permissions;

use Collections\Dictionary;


class GlobalPermissionDictionary
{
	/**
	 * @var Dictionary
	 */
	protected static $dictionary = null;

	/**
	 * @return Dictionary
	 */
	public static function getDictionary()
	{
		if (!self::$dictionary)
			self::setDictionary(new Dictionary());

		return self::$dictionary;
	}

	/**
	 * @param Dictionary $dictionary
	 */
	public static function setDictionary(Dictionary $dictionary)
	{
		self::$dictionary = $dictionary;
	}

	/**
	 * @param Permission $permission
	 */
	public static function addPermission(Permission $permission)
	{
		self::getDictionary()[$permission->getName()] = $permission;
	}

	/**
	 * @param string $name
	 *
	 * @return Permission
	 */
	public static function getPermission(string $name)
	{
		return self::$dictionary[$name];
	}
}