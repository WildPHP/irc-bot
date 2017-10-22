<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\AccountNotifyHandler;

class AccountNotifyHandlerTest extends TestCase
{
	public function testUpdateUserIrcAccount()
	{
		$componentContainer = new \WildPHP\Core\ComponentContainer();
		$channelCollection = new \WildPHP\Core\Channels\ChannelCollection();
		$componentContainer->add($channelCollection);
		$componentContainer->add(new \WildPHP\Core\EventEmitter());
		$userCollection = new \WildPHP\Core\Users\UserCollection();
		$channelCollection->append(new \WildPHP\Core\Channels\Channel('#test', $userCollection, new \WildPHP\Core\Channels\ChannelModes('')));
		$user = new \WildPHP\Core\Users\User('Test');
		$userCollection->append($user);

		$account = new \WildPHP\Core\Connection\IRCMessages\ACCOUNT('ing');
		$account->setPrefix(new \WildPHP\Core\Connection\UserPrefix('Test'));

		$accountNotifyHandler = new AccountNotifyHandler($componentContainer);
		$accountNotifyHandler->updateUserIrcAccount($account, new \WildPHP\Core\Connection\Queue());

		self::assertEquals('ing', $user->getIrcAccount());
	}

	public function testIsCompatible()
	{
		if (!defined('WPHP_VERSION'))
			define('WPHP_VERSION', '3.0.0');

		self::assertEquals(WPHP_VERSION, AccountNotifyHandler::getSupportedVersionConstraint());
	}
}
