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

		self::assertEquals([$this, 'command'], $command->getCallback());
		self::assertEquals(null, $command->getHelp());
		self::assertEquals(-1, $command->getMinimumArguments());
		self::assertEquals(-1, $command->getMaximumArguments());
		self::assertEquals('', $command->getRequiredPermission());

		self::assertEquals($expectedCommand, $command);
	}

	public function testCommandCreateWithPermissionAndHelp()
	{
		$commandHelp = new \WildPHP\Core\Commands\CommandHelp(['Required permission: test']);
		$expectedCommand = new \WildPHP\Core\Commands\Command([$this, 'command'], $commandHelp, -1, -1, 'test');

		$commandHelp = new \WildPHP\Core\Commands\CommandHelp();
		$command = CommandFactory::create([$this, 'command'], $commandHelp, -1, -1, 'test');

		self::assertEquals([$this, 'command'], $command->getCallback());
		self::assertEquals($commandHelp, $command->getHelp());
		self::assertEquals(-1, $command->getMinimumArguments());
		self::assertEquals(-1, $command->getMaximumArguments());
		self::assertEquals('test', $command->getRequiredPermission());
		self::assertEquals($expectedCommand, $command);
	}

	public function command()
	{

	}
}
