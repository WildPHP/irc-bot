<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use ValidationClosures\Types;
use Yoshi2889\Collections\Collection;

class CommandHelp extends Collection
{
	public function __construct(array $initialValues = [])
	{
		parent::__construct(Types::string(), $initialValues);
	}
}