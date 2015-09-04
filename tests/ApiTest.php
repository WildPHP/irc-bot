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

use \WildPHP\Api;

class ApiTest extends PHPUnit_Framework_TestCase
{
	public function testGetSetConfigurationStorage()
	{
		$api = new Api();

		$config = new \WildPHP\Configuration\ConfigurationStorage(dirname(__FILE__) . '/config.test.neon');
		$api->setConfigurationStorage($config);

		$configFromApi = $api->getConfigurationStorage();
		$this->assertSame($config, $configFromApi);
	}

	public function testGetSetModuleEmitter()
	{
		$api = new Api();

		// The ModuleEmitter needs a storage.
		$config = new \WildPHP\Configuration\ConfigurationStorage(dirname(__FILE__) . '/config.test.neon');
		$api->setConfigurationStorage($config);

		// Test if creating a default instance works.
		$moduleEmitter = $api->getModuleEmitter();
		$this->assertInstanceOf('\WildPHP\ModuleEmitter', $moduleEmitter);

		// Now set the new one, and check if we get the same thing back.
		$emitter = new \WildPHP\ModuleEmitter($api);
		$api->setModuleEmitter($emitter);
		$moduleEmitterFromApi = $api->getModuleEmitter();

		$this->assertSame($emitter, $moduleEmitterFromApi);
	}

	public function testGetSetGenerator()
	{
		$api = new Api();

		$generator = $api->getGenerator();
		$this->assertInstanceOf('\Phergie\Irc\Generator', $generator);

		// And set a new one.
		$generator = new \Phergie\Irc\Generator();
		$api->setGenerator($generator);
		$generatorFromApi = $api->getGenerator();
		$this->assertSame($generator, $generatorFromApi);
	}

	public function testGetSetParser()
	{
		$api = new Api();

		$parser = $api->getParser();
		$this->assertInstanceOf('\Phergie\Irc\Parser', $parser);

		// And set a new one.
		$parser = new \Phergie\Irc\Parser();
		$api->setParser($parser);
		$parserFromApi = $api->getParser();
		$this->assertSame($parser, $parserFromApi);
	}
}
