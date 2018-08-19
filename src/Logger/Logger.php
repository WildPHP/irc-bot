<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Logger;

use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Logger extends \Monolog\Logger implements ComponentInterface
{
	use ComponentTrait;
}