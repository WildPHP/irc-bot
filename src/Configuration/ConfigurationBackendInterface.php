<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

interface ConfigurationBackendInterface
{
	/**
	 * @return array
	 */
	public function getAllEntries(): array;
}