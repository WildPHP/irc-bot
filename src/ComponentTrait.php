<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core;

/**
 * Copyright
 * User: rick2
 * Date: 1-5-2017
 * Time: 14:04
 */
trait ComponentTrait
{
	/**
	 * @param ComponentContainer $container
	 *
	 * @return null|object
	 */
	public static function fromContainer(ComponentContainer $container)
	{
		$obj = $container->retrieve(__CLASS__);

		if ($obj && $obj instanceof static)
			return $obj;

		return null;
	}
}