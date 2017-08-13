<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Channels\ChannelModes;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\MODE;
use WildPHP\Core\Connection\IRCMessages\NICK;
use WildPHP\Core\Connection\IRCMessages\PART;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\IRCMessages\QUIT;
use WildPHP\Core\Connection\IRCMessages\RPL_WHOSPCRPL;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\UserPrefix;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;
use WildPHP\Core\Users\UserStateManager;

class UserStateManagerTest extends TestCase
{
	/**
	 * @var \WildPHP\Core\ComponentContainer
	 */
	protected $componentContainer;

	public function setUp()
	{
		$componentContainer = new \WildPHP\Core\ComponentContainer();
		$channelCollection = new ChannelCollection();
		$componentContainer->add($channelCollection);
		$componentContainer->add(new \WildPHP\Core\EventEmitter());
		$componentContainer->add(new Queue());
		$componentContainer->add(new \WildPHP\Core\Logger\Logger('wildphp'));
		$componentContainer->add(
			new \WildPHP\Core\Configuration\Configuration(
				new \WildPHP\Core\Configuration\NeonBackend(dirname(__FILE__) . '/emptyconfig.neon')
			)
		);
		\WildPHP\Core\Configuration\Configuration::fromContainer($componentContainer)['currentNickname'] = 'Test';
		\WildPHP\Core\Configuration\Configuration::fromContainer($componentContainer)['serverConfig']['prefix'] = '(ov)@+';
		$this->componentContainer = $componentContainer;
		
		$channelCollection->append(new Channel('#test', new UserCollection(), new ChannelModes('(ov)@+')));
	}

	public function testUserPart()
	{
		$userStateManager = new UserStateManager($this->componentContainer);
		
		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		$userCollection = $channel->getUserCollection();
		
		$user = new User('Testing');
		$userCollection->append($user);
		
		$part = new PART('#test');
		$part->setNickname('Testing');
		
		$userStateManager->processUserPart($part);
		$userStateManager->processUserPart($part);
		
		self::assertFalse($userCollection->contains($user));

		$user = new User('Test');
		$userCollection->append($user);

		$part = new PART('#test');
		$part->setNickname('Test');

		$userStateManager->processUserPart($part);

		self::assertFalse($userCollection->contains($user));
		self::assertFalse(ChannelCollection::fromContainer($this->componentContainer)->containsChannelName('#test'));
	}

	public function testUserKick()
	{
		$userStateManager = new UserStateManager($this->componentContainer);

		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		$userCollection = $channel->getUserCollection();

		$user = new User('Testing');
		$userCollection->append($user);

		$kick = new KICK('#test', 'Testing', 'Test');

		$userStateManager->processUserKick($kick);
		$userStateManager->processUserKick($kick);

		self::assertFalse($userCollection->contains($user));

		$user = new User('Test');
		$userCollection->append($user);

		$kick = new KICK('#test', 'Test', 'Test');

		$userStateManager->processUserKick($kick);

		self::assertFalse($userCollection->contains($user));
		self::assertFalse(ChannelCollection::fromContainer($this->componentContainer)->containsChannelName('#test'));
	}

	public function testUserJoin()
	{
		$join = new JOIN('#someChannel');
		$join->setNickname('Test');
		$join->setPrefix(new UserPrefix('Test', 'test', 'test'));
		
		$userStateManager = new UserStateManager($this->componentContainer);
		
		$userStateManager->processUserJoin($join, Queue::fromContainer($this->componentContainer));
		
		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#someChannel');
		self::assertInstanceOf(Channel::class, $channel);
		self::assertInstanceOf(User::class, $channel->getUserCollection()->findByNickname('Test'));
	}

	public function testProcessNames()
	{
		$userStateManager = new UserStateManager($this->componentContainer);

		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		
		$names = new \WildPHP\Core\Connection\IRCMessages\RPL_NAMREPLY();
		$names->setNicknames(['+Test', '@Testing']);
		$names->setChannel('#test');
		
		$userStateManager->processNamesReply($names);
		
		$user = $channel->getUserCollection()->findByNickname('Testing');
		self::assertInstanceOf(User::class, $user);
		self::assertTrue($channel->getChannelModes()->isUserInMode('o', $user));
	}

	public function testProcessWhox()
	{
		$userStateManager = new UserStateManager($this->componentContainer);

		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		$user = new User('Testing');
		$channel->getUserCollection()->append($user);

		$whox = new RPL_WHOSPCRPL();
		$whox->setChannel('#test');
		$whox->setNickname('Testing');
		$whox->setHostname('test');
		$whox->setAccountname('*');
		$whox->setUsername('test');
		
		$userStateManager->processWhoxReply($whox);
		
		self::assertEquals('test', $user->getUsername());
		self::assertEquals('test', $user->getHostname());
		self::assertEquals('*', $user->getIrcAccount());
	}

	public function testProcessUserQuit()
	{
		$userStateManager = new UserStateManager($this->componentContainer);

		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		$userCollection = $channel->getUserCollection();

		$user = new User('Testing');
		$userCollection->append($user);

		$quit = new QUIT('Test');
		$quit->setNickname('Testing');

		$userStateManager->processUserQuit($quit, Queue::fromContainer($this->componentContainer));
		$userStateManager->processUserQuit($quit, Queue::fromContainer($this->componentContainer));

		self::assertFalse($userCollection->contains($user));
	}

	public function testProcessUserNicknameChange()
	{
		$userStateManager = new UserStateManager($this->componentContainer);

		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		$userCollection = $channel->getUserCollection();

		$user = new User('Test');
		$userCollection->append($user);
		
		$nick = new NICK('Tester');
		$nick->setNickname('Test');
		
		$userStateManager->processUserNicknameChange($nick, Queue::fromContainer($this->componentContainer));
		
		self::assertEquals('Tester', $user->getNickname());
	}

	public function testProcessUserModeChange()
	{
		$userStateManager = new UserStateManager($this->componentContainer);

		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('#test');
		$userCollection = $channel->getUserCollection();

		$user = new User('Testing');
		$userCollection->append($user);

		self::assertFalse($channel->getChannelModes()->isUserInMode('o', $user));
		
		$mode = new MODE('#test', '+o', ['Testing']);
		$userStateManager->processUserModeChange($mode, Queue::fromContainer($this->componentContainer));
		
		self::assertTrue($channel->getChannelModes()->isUserInMode('o', $user));

		$mode = new MODE('#test', '-o', ['Testing']);
		$userStateManager->processUserModeChange($mode, Queue::fromContainer($this->componentContainer));

		self::assertFalse($channel->getChannelModes()->isUserInMode('o', $user));
	}

	public function testProcessConversation()
	{
		$userStateManager = new UserStateManager($this->componentContainer);
		
		$privmsg = new PRIVMSG('Test', 'ing');
		$privmsg->setNickname('Testing');
		$privmsg->setPrefix(new UserPrefix('Test', 'test', 'test'));
		
		$userStateManager->processConversation($privmsg, Queue::fromContainer($this->componentContainer));
		
		$channel = ChannelCollection::fromContainer($this->componentContainer)->findByChannelName('Testing');
		self::assertInstanceOf(Channel::class, $channel);
		
		$user = $channel->getUserCollection()->findByNickname('Testing');
		self::assertInstanceOf(User::class, $user);
	}
}
