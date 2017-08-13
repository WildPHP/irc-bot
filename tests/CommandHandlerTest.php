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
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Channels\ChannelModes;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\NeonBackend;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Permissions\PermissionGroupCollection;
use WildPHP\Core\Permissions\Validator;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class CommandHandlerTest extends TestCase
{
	protected $componentContainer;

	public function setUp()
	{
		$componentContainer = new ComponentContainer();
		$eventEmitter = new \WildPHP\Core\EventEmitter();
		$componentContainer->add($eventEmitter);
		$componentContainer->add(new Logger('wildphp'));
		$componentContainer->add(new Validator($eventEmitter, new PermissionGroupCollection(), 'Tester'));
		$channelCollection = new ChannelCollection();
		$componentContainer->add($channelCollection);
		$componentContainer->add(new Queue());
		$componentContainer->add(
			new Configuration(
				new NeonBackend(dirname(__FILE__) . '/emptyconfig.neon')
			)
		);
		Configuration::fromContainer($componentContainer)['currentNickname'] = 'Tester';
		Configuration::fromContainer($componentContainer)['prefix'] = '!';
		Configuration::fromContainer($componentContainer)['serverConfig']['prefix'] = '(ov)@+';
		$this->componentContainer = $componentContainer;

		$userCollection = new UserCollection();
		$userCollection->append(new User('Test', 'test', 'test', 'Tester'));
		$userCollection->append(new User('Testing', 'test', 'test', 'Testing'));
		$channelCollection->append(new Channel('#test', $userCollection, new ChannelModes('(ov)@+')));
	}

	public function testRegisterCommand()
	{
		$collection = new \Yoshi2889\Collections\Collection(Types::instanceof(\WildPHP\Core\Commands\Command::class));
		$commandHandler = new CommandHandler($this->componentContainer, $collection);
		
		self::assertEquals(0, $collection->count());
		$commandHelp = new CommandHelp();
		$commandHelp->append('Test');
		
		$expectedCommand = new \WildPHP\Core\Commands\Command([$this, 'command'], $commandHelp, 0, 0, 'test');
		
		self::assertTrue($commandHandler->registerCommand('test', [$this, 'command'], $commandHelp, 0, 0, 'test'));
		self::assertEquals(1, $collection->count());
		self::assertEquals($expectedCommand, $collection['test']);
		
		self::assertFalse($commandHandler->registerCommand('test', [$this, 'command'], $commandHelp, 0, 0, 'test'));
	}

	public function testAlias()
	{
		$collection = new \Yoshi2889\Collections\Collection(Types::instanceof(\WildPHP\Core\Commands\Command::class));
		$commandHandler = new CommandHandler($this->componentContainer, $collection);

		self::assertEquals(0, $collection->count());
		$commandHelp = new CommandHelp();
		$commandHelp->append('Test');

		$expectedCommand = new \WildPHP\Core\Commands\Command([$this, 'command'], $commandHelp, 0, 0, 'test');

		self::assertTrue($commandHandler->registerCommand('test', [$this, 'command'], $commandHelp, 0, 0, 'test'));
		self::assertTrue($commandHandler->alias('test', 'ing'));
		self::assertFalse($commandHandler->alias('tester', 'testering'));
		self::assertFalse($commandHandler->alias('test', 'ing'));
		
		self::assertEquals($expectedCommand, $collection['ing']);
	}
	
	public function testParseAndRun()
	{
		$collection = new \Yoshi2889\Collections\Collection(Types::instanceof(\WildPHP\Core\Commands\Command::class));
		$commandHandler = new CommandHandler($this->componentContainer, $collection);

		$commandHelp = new CommandHelp();
		$commandHelp->append('Test');

		$commandHandler->registerCommand('test', [$this, 'command'], $commandHelp, 0, 0, 'test');
		$commandHandler->registerCommand('test2', [$this, 'command2'], $commandHelp, 0, 0, 'test');
		
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', '!test');
		$privmsg->setNickname('Test');
		
		self::expectOutputString('Hello world!');
		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// Too many arguments
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', '!test2 ing');
		$privmsg->setNickname('Test');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// No permission
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', '!test2');
		$privmsg->setNickname('Testing');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// No command.
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', 'test2');
		$privmsg->setNickname('Test');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// Nonexisting command.
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', '!testing');
		$privmsg->setNickname('Test');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// Only prefix
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', '!');
		$privmsg->setNickname('Test');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// Channel doesn't exist in collection.
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#testing', '!test2');
		$privmsg->setNickname('Test');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));

		// User doesn't exist in collection.
		$privmsg = new \WildPHP\Core\Connection\IRCMessages\PRIVMSG('#test', '!test2');
		$privmsg->setNickname('Foo');

		$commandHandler->parseAndRunCommand($privmsg, Queue::fromContainer($this->componentContainer));
	}

	public function command(Channel $channel, User $user, array $args, ComponentContainer $container, string $command)
	{
		echo 'Hello world!';
	}

	public function command2(Channel $channel, User $user, array $args, ComponentContainer $container, string $command)
	{
		self::fail('Command should not have been run');
	}
}
