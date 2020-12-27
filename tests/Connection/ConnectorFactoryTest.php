<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Connection;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\SecureConnector;
use WildPHP\Core\Connection\ConnectorFactory;

class ConnectorFactoryTest extends TestCase
{
    public function testGetConnector()
    {
        $loop = Factory::create();
        $connector = ConnectorFactory::create($loop);

        self::assertInstanceOf(Connector::class, $connector);
    }
}
