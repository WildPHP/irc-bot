<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Modules;

use WildPHP\Core\ComponentContainer;
use Yoshi2889\Container\ComponentTrait;

abstract class BaseModule implements ModuleInterface
{
	use ComponentTrait;

	/**
	 * BaseModule constructor.
	 *
	 * @param ComponentContainer $container
	 */
	abstract public function __construct(ComponentContainer $container);
}