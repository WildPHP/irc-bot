<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Users\BotStateManager;

class BotStateManagerTest extends TestCase
{
	/**
	 * @var \WildPHP\Core\ComponentContainer
	 */
	protected $container;
	
	
	public function setUp()
	{
		$this->container = new \WildPHP\Core\ComponentContainer();
		$this->container->add(new \WildPHP\Core\EventEmitter());
		$this->container->add(new \WildPHP\Core\Logger\Logger('wildphp'));
		$neonBackend = new \WildPHP\Core\Configuration\NeonBackend(dirname(__FILE__) . '/emptyconfig.neon');
		$configuration = new \WildPHP\Core\Configuration\Configuration($neonBackend);
		$configuration['currentNickname'] = 'Test';
		$this->container->add($configuration);
	}

	public function testMonitorOwnNickname()
	{
		$botStateManager = new BotStateManager($this->container);
		
		$channel = new \WildPHP\Core\Channels\Channel('#test', new \WildPHP\Core\Users\UserCollection(), new \WildPHP\Core\Channels\ChannelModes(''));
		$user = new \WildPHP\Core\Users\User('Test');
		$oldNickname = 'Test';
		$newNickname = 'Testing';
		$botStateManager->monitorOwnNickname($channel, $user, $oldNickname, $newNickname);
		
		self::assertEquals('Testing', \WildPHP\Core\Configuration\Configuration::fromContainer($this->container)['currentNickname']);

		$channel = new \WildPHP\Core\Channels\Channel('#test', new \WildPHP\Core\Users\UserCollection(), new \WildPHP\Core\Channels\ChannelModes(''));
		$user = new \WildPHP\Core\Users\User('Test');
		$oldNickname = 'Test';
		$newNickname = 'Testing123';
		$botStateManager->monitorOwnNickname($channel, $user, $oldNickname, $newNickname);

		self::assertEquals('Testing', \WildPHP\Core\Configuration\Configuration::fromContainer($this->container)['currentNickname']);
	}
}
