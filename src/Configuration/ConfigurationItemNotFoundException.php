<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;


use Throwable;

class ConfigurationItemNotFoundException extends \Exception
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		trigger_error('Use Yoshi2889\\Collections\\NotFoundException instead.', E_USER_DEPRECATED);
		parent::__construct($message, $code, $previous);
	}
}