<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use \WildPHP\Configuration\ConfigurationStorage;

class ConfigurationStorageTest extends PHPUnit_Framework_TestCase
{
	public function testReadConfiguration()
	{
		$storage = new ConfigurationStorage(dirname(__FILE__) . '/config.test.neon');

		$value = $storage->get('phpunit.test');
		$this->assertSame('Hello world!', $value);

		$value = $storage->get('specialchars');
		$this->assertSame('!@#$%^special', $value);
	}

	public function testSetConfiguration()
	{
		$storage = new ConfigurationStorage(dirname(__FILE__) . '/config.test.neon');

		$storage->set('test.key', 'My value');

		$value = $storage->get('test.key');
		$this->assertSame('My value', $value);
	}
}
