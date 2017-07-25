<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Commands\CommandFactory;

class CommandFactoryTest extends TestCase
{
	public function testCommandCreate()
	{
		$expectedCommand = new \WildPHP\Core\Commands\Command([$this, 'command'], null);
		$command = CommandFactory::create([$this, 'command']);

		self::assertEquals($expectedCommand, $command);
	}

	public function command()
	{

	}
}
