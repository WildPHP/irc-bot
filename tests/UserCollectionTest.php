<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Observers\User;
use WildPHP\Core\Observers\UserCollection;

class UserCollectionTest extends TestCase
{
	public function testContainsNickname()
	{
		$userCollection = new UserCollection();
		$userCollection->append(new User('tester'));
		
		self::assertTrue($userCollection->containsNickname('tester'));
		self::assertFalse($userCollection->containsNickname('testing'));
	}

	public function testGetAllNicknames()
	{
		$userCollection = new UserCollection();
		$userCollection->append(new User('tester'));
		
		self::assertEquals(['tester'], $userCollection->getAllNicknames());
	}
}
