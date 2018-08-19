<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

class NumericParameter extends Parameter
{
	/**
	 * NumericParameter constructor.
	 */
	public function __construct()
	{
		parent::__construct(\Closure::fromCallable('is_numeric'));
	}
}