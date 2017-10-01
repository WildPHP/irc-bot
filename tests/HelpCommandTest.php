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
use WildPHP\Core\Commands\HelpCommand;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;
use Yoshi2889\Collections\Collection;

class HelpCommandTest extends TestCase
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
		if (!defined('WPHP_ROOT_DIR'))
			define('WPHP_ROOT_DIR', dirname(__FILE__));
		$this->container = new ComponentContainer();
		$this->container->add(new EventEmitter());
		$this->container->add(new Logger('wildphp'));
		$this->container->add(new CommandHandler($this->container, new Collection(Types:: instanceof (Command::class))));
		$this->container->add(new Queue());

		$this->channel = new Channel('#test', new UserCollection(), new ChannelModes(''));
		$this->user = new User('Tester', '', '', 'testUser');
		$this->channel->getUserCollection()
			->append($this->user);
	}

	public function testLsCommandsCommand()
	{
		$helpCommand = new HelpCommand($this->container);
		
		$helpCommand->lscommandsCommand($this->channel, $this->user, [], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testHelpCommand()
	{
		$helpCommand = new HelpCommand($this->container);

		$helpCommand->helpCommand($this->channel, $this->user, [], $this->container);
		self::assertEquals(2, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();

		$helpCommand->helpCommand($this->channel, $this->user, ['command' => 'cmdhelp'], $this->container);
		self::assertEquals(2, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();

		$helpCommand->helpCommand($this->channel, $this->user, ['command' => 1], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();

		$helpCommand->helpCommand($this->channel, $this->user, ['command' => 'test'], $this->container);
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}
	
	public function testIsCompatible()
	{
		if (!defined('WPHP_VERSION'))
			define('WPHP_VERSION', '3.0.0');

		self::assertEquals(WPHP_VERSION, HelpCommand::getSupportedVersionConstraint());
	}
}
