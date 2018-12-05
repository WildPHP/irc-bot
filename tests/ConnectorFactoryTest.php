<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Observers\ConnectorFactory;

class ConnectorFactoryTest extends TestCase
{
	public function testGetConnector()
	{
		$loop = \React\EventLoop\Factory::create();
		$connector = ConnectorFactory::create($loop);
		
		self::assertInstanceOf(\React\Socket\Connector::class, $connector);
	}

	public function testGetSecureConnector()
	{
		$loop = \React\EventLoop\Factory::create();
		$connector = ConnectorFactory::create($loop, true);

		self::assertInstanceOf(\React\Socket\SecureConnector::class, $connector);
	}
}
