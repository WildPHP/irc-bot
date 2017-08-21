<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use ValidationClosures\Types;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelModes;
use WildPHP\Core\Commands\Command;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\NeonBackend;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Management\ManagementCommands;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;
use Yoshi2889\Collections\Collection;

class ManagementCommandsTest extends TestCase
{
	/**
	 * @var Channel
	 */
	protected $channel;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var ComponentContainer
	 */
	protected $container;
	
	public function setUp()
	{
		$this->container = new ComponentContainer();
		$this->container->add(new EventEmitter());
		$this->container->add(new Logger('wildphp'));
		$this->container->add(new CommandHandler($this->container, new Collection(Types::instanceof(Command::class))));
		
		$neonBackend = new NeonBackend(dirname(__FILE__) . '/emptyconfig.neon');
		$configuration = new Configuration($neonBackend);
		$configuration['serverConfig']['chantypes'] = '#';
		$this->container->add($configuration);
		
		$this->container->add(new Queue());
		
		$this->channel = new Channel('#test', new UserCollection(), new ChannelModes(''));
		$this->user = new User('Tester');
	}

	public function testQuitCommand()
	{
		$managementCommands = new ManagementCommands($this->container);
		
		$managementCommands->quitCommand($this->channel, $this->user, ['Test'], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testJoinCommand()
	{
		$managementCommands = new ManagementCommands($this->container);

		$managementCommands->joinCommand($this->channel, $this->user, ['Test', '#channel'], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testPartCommand()
	{
		$managementCommands = new ManagementCommands($this->container);

		$managementCommands->partCommand($this->channel, $this->user, ['Test', '#channel'], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testNickCommand()
	{
		$managementCommands = new ManagementCommands($this->container);

		$managementCommands->nickCommand($this->channel, $this->user, ['Test'], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testClearQueueCommand()
	{
		$managementCommands = new ManagementCommands($this->container);

		Queue::fromContainer($this->container)->raw('Test');
		Queue::fromContainer($this->container)->raw('Test');
		Queue::fromContainer($this->container)->raw('Test');
		self::assertEquals(3, Queue::fromContainer($this->container)->count());
		
		$managementCommands->joinCommand($this->channel, $this->user, ['Test', '#channel'], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testIsCompatible()
	{
		if (!defined('WPHP_VERSION'))
			define('WPHP_VERSION', '3.0.0');

		self::assertEquals(WPHP_VERSION, ManagementCommands::getSupportedVersionConstraint());
	}
}
