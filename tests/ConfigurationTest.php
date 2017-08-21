<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Configuration\Configuration;

class ConfigurationTest extends TestCase
{
	public function testNeonBackend()
	{
		$path = dirname(__FILE__) . '/testconfig.neon';
		$neonBackend = new \WildPHP\Core\Configuration\NeonBackend($path);
		static::assertEquals($path, $neonBackend->getConfigFile());

		$allEntries = $neonBackend->getAllEntries();
		static::assertEquals(['test' => ['ing' => 'data', 'array' => ['test', 'ing']], 'owner' => 'SomeUser'], $allEntries);

	}
	public function testConfigurationStorage()
	{
		$neonBackend = new \WildPHP\Core\Configuration\NeonBackend(dirname(__FILE__) . '/testconfig.neon');
		$configurationStorage = new Configuration($neonBackend);
		self::assertEquals($neonBackend, $configurationStorage->getBackend());

		$configurationItem = $configurationStorage['test']['ing'];
		$expected = 'data';

		static::assertEquals($expected, $configurationItem);
	}

	public function testPutConfigurationStorage()
	{
		$configurationStorage = new Configuration(new \WildPHP\Core\Configuration\NeonBackend(dirname(__FILE__) . '/testconfig.neon'));

		$configurationStorage['testmore'] = 'Test';
		$expected = 'Test';

		$configurationItem = $configurationStorage['testmore'];

		static::assertEquals($expected, $configurationItem);
	}
}