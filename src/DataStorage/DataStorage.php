<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\DataStorage;


use Flintstone\Flintstone;

/**
 * This class exists solely so that if we ever decide to later on move
 * to a different storage platform, it does not break all modules
 * or require other radical changes.
 */
class DataStorage extends Flintstone
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, $config);
	}
}