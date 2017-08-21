<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Permissions\PermissionGroup;

class PermissionGroupTest extends TestCase
{
	public function testRestoreState()
	{
		$state = [
			'modeGroup' => 1,
			'userCollection' => [
				'user1',
				'user2'
			],
			'allowedPermissions' => [
				'test',
				'ing'
			],
			'channelCollection' => [
				'#channel',
				'#anotherChannel'
			]
		];
		
		$permissionGroup = new PermissionGroup($state);
		
		self::assertEquals(['user1', 'user2'], $permissionGroup->getUserCollection()->getArrayCopy());
		self::assertEquals(['test', 'ing'], $permissionGroup->getAllowedPermissions()->getArrayCopy());
		self::assertEquals(['#channel', '#anotherChannel'], $permissionGroup->getChannelCollection()->getArrayCopy());
	}

	public function testSaveSate()
	{
		$state = [
			'modeGroup' => 1,
			'userCollection' => [
				'user1',
				'user2'
			],
			'allowedPermissions' => [
				'test',
				'ing'
			],
			'channelCollection' => [
				'#channel',
				'#anotherChannel'
			]
		];

		$permissionGroup = new PermissionGroup($state);
		
		self::assertEquals($state, $permissionGroup->toArray());
	}
}
