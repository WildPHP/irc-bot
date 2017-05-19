<?php

namespace WildPHP\Core;

/**
 * Created by PhpStorm.
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