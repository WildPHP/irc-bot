<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Commands\CommandHelp;

class CommandHelpTest extends TestCase
{
	public function testAddPage()
	{
		$commandHelp = new CommandHelp();

		self::assertEquals(0, $commandHelp->count());

		$commandHelp->append('Test!');
		self::assertEquals('Test!', $commandHelp->offsetGet(0));
		self::assertEquals(1, $commandHelp->count());
	}

	public function testIndexExists()
	{
		$commandHelp = new CommandHelp();
		$commandHelp->append('Test!');
		self::assertTrue($commandHelp->offsetExists(0));
		self::assertFalse($commandHelp->offsetExists(1));
	}


}
