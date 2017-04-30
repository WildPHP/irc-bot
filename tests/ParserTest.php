<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use WildPHP\Core\Connection\Parser;
use WildPHP\Core\Connection\IncomingIrcMessage;

class ParserTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$user = new \WildPHP\Core\Users\User();
		$user->setNickname('nickname');
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->add($user);

		$channel = new \WildPHP\Core\Channels\Channel();
		$channel->setName('#someChannel');
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->add($channel);

		\WildPHP\Core\Logger\Logger::initialize('php://stdout');
	}

	public function tearDown()
	{
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->clear();
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->clear();
	}

	public function testSpecializePrivmsg()
	{
		$lineToTest = ':nickname!~user@host PRIVMSG #someChannel :A random message!' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$message = $message->specialize();

		$this->assertInstanceOf('\WildPHP\Core\Connection\IncomingIrcMessages\PRIVMSG', $message);
	}

	public function testAUTHENTICATE()
	{
		$lineToTest = 'AUTHENTICATE +' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$authenticateMessage = \WildPHP\Core\Connection\IncomingIrcMessages\AUTHENTICATE::fromIncomingIrcMessage($message);

		$this->assertEquals('+', $authenticateMessage->getResponse());
	}

	public function testJOIN()
	{
		$user = new \WildPHP\Core\Users\User();
		$user->setNickname('nickname');
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->add($user);

		$channel = new \WildPHP\Core\Channels\Channel();
		$channel->setName('#someChannel');
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->add($channel);

		$expectedUserPrefix = new \WildPHP\Core\Connection\UserPrefix('nickname', '~user', 'host');

		$lineToTest = ':nickname!~user@host JOIN #someChannel extended-join-username :A' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$joinMessage = \WildPHP\Core\Connection\IncomingIrcMessages\JOIN::fromIncomingIrcMessage($message);

		$this->assertSame($user, $joinMessage->getUser());
		$this->assertSame($channel, $joinMessage->getChannels()[0]);
		$this->assertEquals($expectedUserPrefix, $joinMessage->getPrefix());

		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->clear();
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->clear();
	}

	public function testKICK()
	{
		$user = new \WildPHP\Core\Users\User();
		$user->setNickname('nickname');
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->add($user);

		$channel = new \WildPHP\Core\Channels\Channel();
		$channel->setName('#someChannel');
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->add($channel);

		$expectedUserPrefix = new \WildPHP\Core\Connection\UserPrefix('nickname', '~user', 'host');

		$lineToTest = ':nickname!~user@host KICK #someChannel othernickname :message' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$kickMessage = \WildPHP\Core\Connection\IncomingIrcMessages\KICK::fromIncomingIrcMessage($message);

		$this->assertEquals('message', $kickMessage->getMessage());
		$this->assertEquals($expectedUserPrefix, $kickMessage->getPrefix());
		$this->assertSame($user, $kickMessage->getUser());
		$this->assertSame($channel, $kickMessage->getChannel());

		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->clear();
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->clear();
	}

	public function testNOTICE()
	{
		$user = new \WildPHP\Core\Users\User();
		$user->setNickname('nickname');
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->add($user);

		$channel = new \WildPHP\Core\Channels\Channel();
		$channel->setName('#someChannel');
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->add($channel);

		$expectedUserPrefix = new \WildPHP\Core\Connection\UserPrefix('nickname', '~user', 'host');

		$lineToTest = ':nickname!~user@host NOTICE #someChannel :test' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$noticeMessage = \WildPHP\Core\Connection\IncomingIrcMessages\NOTICE::fromIncomingIrcMessage($message);

		$this->assertEquals('test', $noticeMessage->getMessage());
		$this->assertEquals($expectedUserPrefix, $noticeMessage->getPrefix());
		$this->assertSame($user, $noticeMessage->getUser());
		$this->assertSame($channel, $noticeMessage->getChannel());

		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->clear();
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->clear();
	}

	public function testPART()
	{
		$user = new \WildPHP\Core\Users\User();
		$user->setNickname('nickname');
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->add($user);

		$channel = new \WildPHP\Core\Channels\Channel();
		$channel->setName('#someChannel');
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->add($channel);

		$expectedUserPrefix = new \WildPHP\Core\Connection\UserPrefix('nickname', '~user', 'host');

		$lineToTest = ':nickname!~user@host PART #someChannel' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$partMessage = \WildPHP\Core\Connection\IncomingIrcMessages\PART::fromIncomingIrcMessage($message);

		$this->assertSame($user, $partMessage->getUser());
		$this->assertSame($channel, $partMessage->getChannels()[0]);
		$this->assertEquals($expectedUserPrefix, $partMessage->getPrefix());

		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->clear();
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->clear();
	}

	public function testPRIVMSG()
	{
		$user = new \WildPHP\Core\Users\User();
		$user->setNickname('nickname');
		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->add($user);

		$channel = new \WildPHP\Core\Channels\Channel();
		$channel->setName('#someChannel');
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->add($channel);

		$expectedUserPrefix = new \WildPHP\Core\Connection\UserPrefix('nickname', '~user', 'host');

		$lineToTest = ':nickname!~user@host PRIVMSG #someChannel :test' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$privmsgMessage = \WildPHP\Core\Connection\IncomingIrcMessages\PRIVMSG::fromIncomingIrcMessage($message);

		$this->assertEquals('test', $privmsgMessage->getMessage());
		$this->assertEquals($expectedUserPrefix, $privmsgMessage->getPrefix());
		$this->assertSame($user, $privmsgMessage->getUser());
		$this->assertSame($channel, $privmsgMessage->getChannel());

		\WildPHP\Core\Users\UserCollection::getGlobalInstance()->clear();
		\WildPHP\Core\Channels\ChannelCollection::getGlobalInstance()->clear();
	}
}
