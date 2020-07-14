<?php

/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Connection;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\ConnectionDetails;

class ConnectionDetailsTest extends TestCase
{
    public function testGetSetUsername()
    {
        $username = 'Test';

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($username, $connectionDetails->getUsername());
    }

    public function testGetSetHostname()
    {
        $hostname = 'Test';

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($hostname, $connectionDetails->getHostname());
    }

    public function testGetgetRealname()
    {
        $realname = 'Test';

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($realname, $connectionDetails->getRealname());
    }

    public function testGetSetPassword()
    {
        $password = 'Test';

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($password, $connectionDetails->getPassword());
    }

    public function testGetSetPort()
    {
        $port = 9999;

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($port, $connectionDetails->getPort());
    }

    public function testGetSetAddress()
    {
        $address = 'Test';

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($address, $connectionDetails->getAddress());
    }

    public function testGetSetWantedNickname()
    {
        $wantedNickname = 'Test';

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test');
        self::assertEquals($wantedNickname, $connectionDetails->getWantedNickname());
    }

    public function testGetSetSecure()
    {
        $secure = true;

        $connectionDetails = new ConnectionDetails('Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test', true);
        self::assertEquals($secure, $connectionDetails->getSecure());
    }

    public function testGetSetContextOptions()
    {
        $options = ['test'];

        $connectionDetails = new ConnectionDetails(
            'Test', 'Test', 'Test', 9999, 'Test', 'Test', 'Test', true,
            $options
        );
        self::assertEquals($options, $connectionDetails->getContextOptions());
    }

    public function testFromArray()
    {
        $details = [
            'connection' => [
                'server' => 'irc.freenode.net',
                'port' => 6697,
                'secure' => true,
                'nickname' => [
                    'MyBot'
                ],
                'username' => 'MyBot',
                'realname' => 'A WildPHP Bot',
                'password' => 'tester'
            ],
        ];

        $connectionDetails = ConnectionDetails::fromArray($details);

        self::assertEquals('irc.freenode.net', $connectionDetails->getAddress());
        self::assertEquals(6697, $connectionDetails->getPort());
        self::assertTrue($connectionDetails->getSecure());
        self::assertEquals('MyBot', $connectionDetails->getWantedNickname());
        self::assertEquals('MyBot', $connectionDetails->getUsername());
        self::assertEquals('A WildPHP Bot', $connectionDetails->getRealname());
        self::assertEquals('tester', $connectionDetails->getPassword());
    }

    public function testFromArrayMissingRootKey()
    {
        $details = [
            'server' => 'irc.freenode.net',
            'port' => 6697,
            'secure' => true,
            'nickname' => [
                'MyBot'
            ],
            'username' => 'MyBot',
            'realname' => 'A WildPHP Bot',
            'password' => 'tester'
        ];

        $this->expectException(\InvalidArgumentException::class);
        ConnectionDetails::fromArray($details);
    }

    public function testFromArrayMissingMandatoryKeys()
    {
        $details = [
            'connection' => [
                // server and port are missing
                'secure' => true,
                'nickname' => [
                    'MyBot'
                ],
                'username' => 'MyBot',
                'realname' => 'A WildPHP Bot',
                'password' => 'tester'
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        ConnectionDetails::fromArray($details);
    }
}
