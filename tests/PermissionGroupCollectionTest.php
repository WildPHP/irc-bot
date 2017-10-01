<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Permissions\PermissionGroup;
use WildPHP\Core\Permissions\PermissionGroupCollection;

class PermissionGroupCollectionTest extends TestCase
{
	public function setUp()
	{
		if (!defined('WPHP_ROOT_DIR'))
			define('WPHP_ROOT_DIR', dirname(__FILE__));
	}
	
	public function testGetStoredGroupData()
	{
		$permissionGroupCollection = new PermissionGroupCollection();
		
		$expected = [
			'modeGroup' => 0,
			'userCollection' => [
				'ircAccount'
			],
			'allowedPermissions' => [
				'testing'
			],
			'channelCollection' => []
		];
		
		self::assertEquals($expected, $permissionGroupCollection->getStoredGroupData('collectionTestGroup'));
		self::assertNull($permissionGroupCollection->getStoredGroupData('nonexistingGroup'));
	}

	public function testFindGroupsForIrcAccount()
	{
		$ircAccount = 'ircAccount';

		$permissionGroupCollection = new PermissionGroupCollection();
		$groupState = $permissionGroupCollection->getStoredGroupData('collectionTestGroup');
		
		$expectedGroup = new PermissionGroup($groupState);
		$permissionGroupCollection->offsetSet(0, $expectedGroup);
		
		$groups = $permissionGroupCollection->findAllGroupsForIrcAccount($ircAccount);
		
		self::assertEquals($expectedGroup, $groups[0]);
	}

	public function testOffsetUnset()
	{
		$permissionGroupCollection = new PermissionGroupCollection();
		$groupState = $permissionGroupCollection->getStoredGroupData('collectionTestGroup');

		$expectedGroup = new PermissionGroup($groupState);
		$permissionGroupCollection->offsetSet('test', $expectedGroup);
		
		$permissionGroupCollection->offsetUnset('test');
		
		self::assertFalse($permissionGroupCollection->offsetExists('test'));
	}
}
