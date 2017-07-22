<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IRCMessages\AUTHENTICATE;
use WildPHP\Core\Connection\IRCMessages\AWAY;
use WildPHP\Core\Connection\IRCMessages\CAP;
use WildPHP\Core\Connection\IRCMessages\ERROR;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\MODE;
use WildPHP\Core\Connection\IRCMessages\NICK;
use WildPHP\Core\Connection\IRCMessages\NOTICE;
use WildPHP\Core\Connection\IRCMessages\PART;
use WildPHP\Core\Connection\IRCMessages\PASS;
use WildPHP\Core\Connection\IRCMessages\PING;
use WildPHP\Core\Connection\IRCMessages\PONG;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\IRCMessages\QUIT;
use WildPHP\Core\Connection\IRCMessages\RAW;
use WildPHP\Core\Connection\IRCMessages\TOPIC;
use WildPHP\Core\Connection\IRCMessages\USER;
use WildPHP\Core\Connection\IRCMessages\WHO;
use WildPHP\Core\Connection\Parser;

class IrcMessageTest extends TestCase
{
	public function testAuthenticateCreate()
	{
		$authenticate = new AUTHENTICATE('+');

		static::assertEquals('+', $authenticate->getResponse());

		$expected = 'AUTHENTICATE +' . "\r\n";
		static::assertEquals($expected, $authenticate->__toString());
	}

	public function testAuthenticateReceive()
	{
		$line = Parser::parseLine('AUTHENTICATE +' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$authenticate = AUTHENTICATE::fromIncomingIrcMessage($incoming);

		static::assertEquals('+', $authenticate->getResponse());
	}

	public function testAwayCreate()
	{
		$away = new AWAY('A sample message');

		static::assertEquals('A sample message', $away->getMessage());

		$expected = 'AWAY :A sample message' . "\r\n";
		static::assertEquals($expected, $away->__toString());
	}

	public function testAwayReceive()
	{
		$line = Parser::parseLine(':nickname!host AWAY :A sample message' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$away = AWAY::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $away->getNickname());
		static::assertEquals('A sample message', $away->getMessage());
	}

	public function testCapCreate()
    {
        $cap = new CAP('REQ', ['cap1', 'cap2']);

        static::assertEquals('REQ', $cap->getCommand());
        static::assertEquals(['cap1', 'cap2'], $cap->getCapabilities());

        $expected = 'CAP REQ :cap1 cap2' . "\r\n";
        static::assertEquals($expected, $cap->__toString());
    }

    public function testCapReceive()
    {
        $line = Parser::parseLine('CAP * LS :cap1 cap2' . "\r\n");
        $incoming = new IncomingIrcMessage($line, new ComponentContainer());
        $cap = CAP::fromIncomingIrcMessage($incoming);

        static::assertEquals('LS', $cap->getCommand());
        static::assertEquals(['cap1', 'cap2'], $cap->getCapabilities());
        static::assertEquals('*', $cap->getNickname());
    }

	public function testErrorReceive()
	{
		$line = Parser::parseLine('ERROR :A sample message' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$error = ERROR::fromIncomingIrcMessage($incoming);

		static::assertEquals('A sample message', $error->getMessage());
	}

	public function testJoinCreate()
	{
		$join = new JOIN(['#channel1', '#channel2'], ['key1', 'key2']);

		static::assertEquals(['#channel1', '#channel2'], $join->getChannels());
		static::assertEquals(['key1', 'key2'], $join->getKeys());

		$expected = 'JOIN #channel1,#channel2 key1,key2' . "\r\n";
		static::assertEquals($expected, $join->__toString());
	}

	public function testJoinCreateKeyMismatch()
	{
		$this->expectException(InvalidArgumentException::class);

		new JOIN(['#channel1', '#channel2'], ['key1']);
	}

	public function testJoinReceiveExtended()
	{
		$line = Parser::parseLine(':nickname!host JOIN #channel ircAccountName :realname' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$join = JOIN::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $join->getNickname());
		static::assertEquals(['#channel'], $join->getChannels());
		static::assertEquals('ircAccountName', $join->getIrcAccount());
		static::assertEquals('realname', $join->getRealname());
	}

	public function testJoinReceiveRegular()
	{
		$line = Parser::parseLine(':nickname!host JOIN #channel' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$join = JOIN::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $join->getNickname());
		static::assertEquals(['#channel'], $join->getChannels());
		static::assertEquals('', $join->getIrcAccount());
		static::assertEquals('', $join->getRealname());
	}

	public function testKickCreate()
	{
		$kick = new KICK('#channel', 'nickname', 'Bleep you!');

		static::assertEquals('#channel', $kick->getChannel());
		static::assertEquals('nickname', $kick->getTarget());
		static::assertEquals('Bleep you!', $kick->getMessage());

		$expected = 'KICK #channel nickname :Bleep you!' . "\r\n";
		static::assertEquals($expected, $kick->__toString());
	}

	public function testKickReceive()
	{
		$line = Parser::parseLine(':nickname!host KICK #somechannel othernickname :You deserved it!');
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$kick = KICK::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $kick->getNickname());
		static::assertEquals('othernickname', $kick->getTarget());
		static::assertEquals('#somechannel', $kick->getChannel());
		static::assertEquals('You deserved it!', $kick->getMessage());
	}

	public function testModeCreate()
	{
		$mode = new MODE('target', '-o+b', ['arg1', 'arg2']);

		static::assertEquals('target', $mode->getTarget());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals(['arg1', 'arg2'], $mode->getArguments());

		$expected = 'MODE target -o+b arg1 arg2' . "\r\n";
		static::assertEquals($expected, $mode->__toString());
	}

	public function testModeReceiveChannel()
	{
		$line = Parser::parseLine(':nickname!host MODE #channel -o+b arg1 arg2' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$mode = MODE::fromIncomingIrcMessage($incoming);

		static::assertEquals('#channel', $mode->getTarget());
		static::assertEquals('nickname', $mode->getNickname());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals(['arg1', 'arg2'], $mode->getArguments());
	}

	public function testModeReceiveUser()
	{
		$line = Parser::parseLine(':nickname!host MODE user -o+b' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$mode = MODE::fromIncomingIrcMessage($incoming);

		static::assertEquals('user', $mode->getTarget());
		static::assertEquals('nickname', $mode->getNickname());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals([], $mode->getArguments());
	}

	public function testModeReceiveInitial()
	{
		$line = Parser::parseLine(':nickname MODE nickname -o+b' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$mode = MODE::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $mode->getTarget());
		static::assertEquals('nickname', $mode->getNickname());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals([], $mode->getArguments());
	}

	public function testNickCreate()
	{
		$nick = new NICK('newnickname');

		static::assertEquals('newnickname', $nick->getNewNickname());

		$expected = 'NICK newnickname' . "\r\n";
		static::assertEquals($expected, $nick->__toString());
	}

	public function testNickReceive()
	{
		$line = Parser::parseLine(':nickname!host NICK newnickname' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$nick = NICK::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $nick->getNickname());
		static::assertEquals('newnickname', $nick->getNewNickname());
	}

	public function testNoticeCreate()
	{
		$notice = new NOTICE('#somechannel', 'This is a test message');

		static::assertEquals('#somechannel', $notice->getChannel());
		static::assertEquals('This is a test message', $notice->getMessage());

		$expected = 'NOTICE #somechannel :This is a test message' . "\r\n";
		static::assertEquals($expected, $notice->__toString());
	}

	public function testNoticeReceive()
	{
		$line = Parser::parseLine(':nickname!host NOTICE #somechannel :This is a test message' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$notice = NOTICE::fromIncomingIrcMessage($incoming);

		static::assertEquals('#somechannel', $notice->getChannel());
		static::assertEquals('This is a test message', $notice->getMessage());
	}

	public function testPartCreate()
	{
		$part = new PART(['#channel1', '#channel2'], 'I am out');

		static::assertEquals(['#channel1', '#channel2'], $part->getChannels());
		static::assertEquals('I am out', $part->getMessage());

		$expected = 'PART #channel1,#channel2 :I am out' . "\r\n";
		static::assertEquals($expected, $part->__toString());
	}

	public function testPartReceive()
	{
		$line = Parser::parseLine(':nickname!host PART #channel :I have a valid reason' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$part = PART::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $part->getNickname());
		static::assertEquals(['#channel'], $part->getChannels());
		static::assertEquals('I have a valid reason', $part->getMessage());
	}

	public function testPassCreate()
    {
        $pass = new PASS('myseekritpassw0rd');

        static::assertEquals('myseekritpassw0rd', $pass->getPassword());

        $expected = 'PASS :myseekritpassw0rd' . "\r\n";
        static::assertEquals($expected, $pass->__toString());
    }

	public function testPingCreate()
	{
		$ping = new PING('testserver1', 'testserver2');

		static::assertEquals('testserver1', $ping->getServer1());
		static::assertEquals('testserver2', $ping->getServer2());

		$expected = 'PING testserver1 testserver2' . "\r\n";
		static::assertEquals($expected, $ping->__toString());
	}

	public function testPingReceive()
	{
		$line = Parser::parseLine('PING testserver1 testserver2' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$ping = PING::fromIncomingIrcMessage($incoming);

		static::assertEquals('testserver1', $ping->getServer1());
		static::assertEquals('testserver2', $ping->getServer2());
	}

	public function testPongCreate()
	{
		$pong = new PONG('testserver1', 'testserver2');

		static::assertEquals('testserver1', $pong->getServer1());
		static::assertEquals('testserver2', $pong->getServer2());

		$expected = 'PONG testserver1 testserver2' . "\r\n";
		static::assertEquals($expected, $pong->__toString());
	}

	public function testPongReceive()
	{
		$line = Parser::parseLine('PONG testserver1 testserver2' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$pong = PONG::fromIncomingIrcMessage($incoming);

		static::assertEquals('testserver1', $pong->getServer1());
		static::assertEquals('testserver2', $pong->getServer2());
	}

	public function testPrivmsgCreate()
	{
		$privmsg = new PRIVMSG('#somechannel', 'This is a test message');

		static::assertEquals('#somechannel', $privmsg->getChannel());
		static::assertEquals('This is a test message', $privmsg->getMessage());

		$expected = 'PRIVMSG #somechannel :This is a test message' . "\r\n";
		static::assertEquals($expected, $privmsg->__toString());
	}

	public function testPrivmsgReceive()
	{
		$line = Parser::parseLine(':nickname!host PRIVMSG #somechannel :This is a test message' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$privmsg = PRIVMSG::fromIncomingIrcMessage($incoming);

		static::assertEquals('#somechannel', $privmsg->getChannel());
		static::assertEquals('This is a test message', $privmsg->getMessage());
	}

	public function testQuitCreate()
	{
		$quit = new QUIT('A sample message');

		static::assertEquals('A sample message', $quit->getMessage());

		$expected = 'QUIT :A sample message' . "\r\n";
		static::assertEquals($expected, $quit->__toString());
	}

	public function testQuitReceive()
	{
		$line = Parser::parseLine(':nickname!host QUIT :A sample message' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$quit = QUIT::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $quit->getNickname());
		static::assertEquals('A sample message', $quit->getMessage());
	}

	public function testRawCreate()
    {
        $raw = new RAW('a command');

        static::assertEquals('a command', $raw->getCommand());

        $expected = 'a command' . "\r\n";
        static::assertEquals($expected, $raw->__toString());
    }

	public function testTopicCreate()
    {
        $topic = new TOPIC('#someChannel', 'Test message');

        static::assertEquals('#someChannel', $topic->getChannel());
        static::assertEquals('Test message', $topic->getMessage());

        $expected = 'TOPIC #someChannel :Test message' . "\r\n";
        static::assertEquals($expected, $topic->__toString());
    }

    public function testTopicReceive()
    {
        $line = Parser::parseLine(':nickname!host TOPIC #someChannel :This is a new topic' . "\r\n");
        $incoming = new IncomingIrcMessage($line, new ComponentContainer());
        $topic = TOPIC::fromIncomingIrcMessage($incoming);

        static::assertEquals('#someChannel', $topic->getChannel());
        static::assertEquals('This is a new topic', $topic->getMessage());
    }

    public function testUserCreate()
    {
        $user = new USER('myusername', 'localhost', 'someserver', 'arealname');

        static::assertEquals('myusername', $user->getUsername());
        static::assertEquals('localhost', $user->getHostname());
        static::assertEquals('someserver', $user->getServername());
        static::assertEquals('arealname', $user->getRealname());

        $expected = 'USER myusername localhost someserver arealname' . "\r\n";
        static::assertEquals($expected, $user->__toString());
    }

    public function testUserReceive()
    {
        $line = Parser::parseLine('USER myusername localhost someserver arealname' . "\r\n");
        $incoming = new IncomingIrcMessage($line, new ComponentContainer());
        $user = USER::fromIncomingIrcMessage($incoming);

        static::assertEquals('myusername', $user->getUsername());
        static::assertEquals('localhost', $user->getHostname());
        static::assertEquals('someserver', $user->getServername());
        static::assertEquals('arealname', $user->getRealname());
    }

	public function testWhoCreate()
    {
        $who = new WHO('#someChannel', '%nuhaf');

        static::assertEquals('#someChannel', $who->getChannel());
        static::assertEquals('%nuhaf', $who->getOptions());

        $expected = 'WHO #someChannel %nuhaf' . "\r\n";
        static::assertEquals($expected, $who->__toString());
    }

    public function testWhoReceive()
    {
        $line = Parser::parseLine(':nickname!host WHO #someChannel %nuhaf' . "\r\n");
        $incoming = new IncomingIrcMessage($line, new ComponentContainer());
        $who = WHO::fromIncomingIrcMessage($incoming);

        static::assertEquals('#someChannel', $who->getChannel());
        static::assertEquals('%nuhaf', $who->getOptions());
    }
}
