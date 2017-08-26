<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

class StringParameter extends Parameter
{
	/**
	 * StringParameter constructor.
	 */
	public function __construct()
	{
		// Parameter values are string by design. Don't bother validating them.
		parent::__construct(function () { return true; });
	}
}