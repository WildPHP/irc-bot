<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Observers\UserPrefix;

class UserPrefixTest extends TestCase
{
	public function testRegularPrefix()
	{
		$prefix = 'nickname!username@hostname';
		$userPrefixObject = UserPrefix::fromString($prefix);

		$expected = new UserPrefix('nickname', 'username', 'hostname');
		self::assertEquals($expected, $userPrefixObject);

		$prefix = 'nickname!username';
		$userPrefixObject = UserPrefix::fromString($prefix);

		$expected = new UserPrefix('nickname', 'username');
		self::assertEquals($expected, $userPrefixObject);

		$prefix = 'nickname@hostname';
		$userPrefixObject = UserPrefix::fromString($prefix);

		$expected = new UserPrefix('nickname', '', 'hostname');
		self::assertEquals($expected, $userPrefixObject);
	}

	public function testOnlyServerPrefix()
	{
		$prefix = 'irc.example.com';
		$userPrefixObject = UserPrefix::fromString($prefix);

		$expected = new UserPrefix('', '', 'irc.example.com');
		self::assertEquals($expected, $userPrefixObject);
	}
}
