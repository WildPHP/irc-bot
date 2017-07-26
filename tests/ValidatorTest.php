<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Permissions\Validator;

class ValidatorTest extends TestCase
{
	/**
	 * @return Validator
	 */
	public function initValidator(): Validator
	{
		if (!defined('WPHP_ROOT_DIR'))
			define('WPHP_ROOT_DIR', dirname(__FILE__));

		$permGroupCollection = new \WildPHP\Core\Permissions\PermissionGroupCollection();
		$eventEmitter = new \WildPHP\Core\EventEmitter();
		$owner = 'TestUser';
		return new Validator($eventEmitter, $permGroupCollection, $owner);
	}

	public function testParseRPL_ISUPPORT()
	{
		$validator = $this->initValidator();

		$modeDefinitions = '(ov)@+';
		$expectedModes = ['o', 'v'];

		$rpl_isupport = new \WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT();
		$rpl_isupport->setVariables(['prefix' => $modeDefinitions]);

		$validator->createModeGroups($rpl_isupport);

		self::assertEquals($expectedModes, $validator->getModes());
	}

	public function testOwnerHasPermission()
	{
		$validator = $this->initValidator();
		$user = new \WildPHP\Core\Users\User('nickname', '', '', 'TestUser');

		self::assertEquals('owner', $validator->isAllowedTo('test', $user));
	}

	public function testHasModePermission()
	{
		$modeDefinitions = '(ov)@+';
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($modeDefinitions);
		$userCollection = new \WildPHP\Core\Users\UserCollection();
		$channel = new \WildPHP\Core\Channels\Channel('#testChannel', $userCollection, $channelModes);

		$user = new \WildPHP\Core\Users\User('SomeNickname', '', '', 'ircAccount');
		$userCollection->append($user);

		$channelModes->addUserToMode('o', $user);

		// Fake an RPL_ISUPPORT message.
		$rpl_isupport = new \WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT();
		$rpl_isupport->setVariables(['prefix' => $modeDefinitions]);

		$validator = $this->initValidator();
		$validator->createModeGroups($rpl_isupport);

		/** @var \WildPHP\Core\Permissions\PermissionGroup $group */
		$group = $validator->getPermissionGroupCollection()
			->offsetGet('o');
		$group->getAllowedPermissions()
			->append('test');

		self::assertSame('o', $validator->isAllowedTo('test', $user, $channel));
	}

	public function testHasPermission()
	{
		$modeDefinitions = '';
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($modeDefinitions);
		$userCollection = new \WildPHP\Core\Users\UserCollection();
		$channel = new \WildPHP\Core\Channels\Channel('#testChannel', $userCollection, $channelModes);

		$user = new \WildPHP\Core\Users\User('SomeNickname', '', '', 'ircAccount');
		$userCollection->append($user);

		$validator = $this->initValidator();

		$validator->getPermissionGroupCollection()
			->offsetSet('testGroup', new \WildPHP\Core\Permissions\PermissionGroup());

		/** @var \WildPHP\Core\Permissions\PermissionGroup $group */
		$group = $validator->getPermissionGroupCollection()->offsetGet('testGroup');
		$group->getAllowedPermissions()
			->append('test');
		$group->getUserCollection()->append($user->getIrcAccount());

		self::assertSame('testGroup', $validator->isAllowedTo('test', $user, $channel));
	}

	public function testHasCascadedPermission()
	{
		$modeDefinitions = '(ov)@+';
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($modeDefinitions);
		$userCollection = new \WildPHP\Core\Users\UserCollection();
		$channel = new \WildPHP\Core\Channels\Channel('#testChannel', $userCollection, $channelModes);

		$user = new \WildPHP\Core\Users\User('SomeNickname', '', '', 'ircAccount');
		$userCollection->append($user);

		$channelModes->addUserToMode('o', $user);

		// Fake an RPL_ISUPPORT message.
		$rpl_isupport = new \WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT();
		$rpl_isupport->setVariables(['prefix' => $modeDefinitions]);

		$validator = $this->initValidator();
		$validator->createModeGroups($rpl_isupport);

		$validator->getPermissionGroupCollection()
			->offsetSet('testGroup', new \WildPHP\Core\Permissions\PermissionGroup());

		/** @var \WildPHP\Core\Permissions\PermissionGroup $group */
		$group = $validator->getPermissionGroupCollection()->offsetGet('testGroup');
		$group->getAllowedPermissions()
			->append('testing');
		$group->getUserCollection()->append($user->getIrcAccount());

		self::assertSame('testGroup', $validator->isAllowedTo('testing', $user, $channel));
	}
}
