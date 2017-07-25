<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\ComponentContainer;

class ComponentContainerTest extends TestCase
{
	public function testGetSetLoop()
	{
		$loop = \React\EventLoop\Factory::create();

		$container = new ComponentContainer();
		$container->setLoop($loop);

		self::assertSame($loop, $container->getLoop());
	}
}
