<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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