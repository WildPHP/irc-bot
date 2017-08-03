<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\ConnectionDetails;

class ConnectionDetailsTest extends TestCase
{
	public function testGetSetUsername()
	{
		$username = 'Test';
		
		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setUsername($username);
		self::assertEquals($username, $connectionDetails->getUsername());
	}

	public function testGetSetHostname()
	{
		$hostname = 'Test';

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setHostname($hostname);
		self::assertEquals($hostname, $connectionDetails->getHostname());
	}

	public function testGetgetRealname()
	{
		$realname = 'Test';

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setRealname($realname);
		self::assertEquals($realname, $connectionDetails->getRealname());
	}

	public function testGetSetPassword()
	{
		$password = 'Test';

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setPassword($password);
		self::assertEquals($password, $connectionDetails->getPassword());
	}

	public function testGetSetPort()
	{
		$port = 1234;

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setPort($port);
		self::assertEquals($port, $connectionDetails->getPort());
	}

	public function testGetSetAddress()
	{
		$address = 'Test';

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setAddress($address);
		self::assertEquals($address, $connectionDetails->getAddress());
	}

	public function testGetSetWantedNickname()
	{
		$wantedNickname = 'Test';

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setWantedNickname($wantedNickname);
		self::assertEquals($wantedNickname, $connectionDetails->getWantedNickname());
	}

	public function testGetSetSecure()
	{
		$secure = true;

		$connectionDetails = new ConnectionDetails();
		$connectionDetails->setSecure($secure);
		self::assertEquals($secure, $connectionDetails->getSecure());
	}
}
