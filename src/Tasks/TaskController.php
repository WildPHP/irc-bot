<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Tasks;

use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class TaskController extends \Yoshi2889\Tasks\TaskController implements ComponentInterface
{
	use ComponentTrait;
}