<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Modules;

use Yoshi2889\Container\ComponentInterface;

interface ModuleInterface extends ComponentInterface
{
	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string;
}