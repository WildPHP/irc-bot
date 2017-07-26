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
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IRCMessages\ACCOUNT;
use WildPHP\Core\Connection\IRCMessages\AUTHENTICATE;
use WildPHP\Core\Connection\IRCMessages\AWAY;
use WildPHP\Core\Connection\IRCMessages\CAP;
use WildPHP\Core\Connection\IRCMessages\ERROR;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\MODE;
use WildPHP\Core\Connection\IRCMessages\NAMES;
use WildPHP\Core\Connection\IRCMessages\NICK;
use WildPHP\Core\Connection\IRCMessages\NOTICE;
use WildPHP\Core\Connection\IRCMessages\PART;
use WildPHP\Core\Connection\IRCMessages\PASS;
use WildPHP\Core\Connection\IRCMessages\PING;
use WildPHP\Core\Connection\IRCMessages\PONG;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\IRCMessages\QUIT;
use WildPHP\Core\Connection\IRCMessages\RAW;
use WildPHP\Core\Connection\IRCMessages\RPL_ENDOFNAMES;
use WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT;
use WildPHP\Core\Connection\IRCMessages\RPL_NAMREPLY;
use WildPHP\Core\Connection\IRCMessages\RPL_TOPIC;
use WildPHP\Core\Connection\IRCMessages\RPL_WELCOME;
use WildPHP\Core\Connection\IRCMessages\RPL_WHOSPCRPL;
use WildPHP\Core\Connection\IRCMessages\TOPIC;
use WildPHP\Core\Connection\IRCMessages\USER;
use WildPHP\Core\Connection\IRCMessages\VERSION;
use WildPHP\Core\Connection\IRCMessages\WHO;
use WildPHP\Core\Connection\IRCMessages\WHOIS;
use WildPHP\Core\Connection\IRCMessages\WHOWAS;
use WildPHP\Core\Connection\Parser;
use WildPHP\Core\Connection\UserPrefix;

class IrcMessageTest extends TestCase
{
	public function testAccountCreate()
	{
		$account = new ACCOUNT('ircAccount');

		static::assertEquals('ircAccount', $account->getAccountName());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		ACCOUNT::fromIncomingIrcMessage($incomingIrcMessage);
	}

	public function testAccountReceive()
	{
		$line = Parser::parseLine(':nickname!username@hostname ACCOUNT ircAccount' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$account = ACCOUNT::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $account->getPrefix());
		static::assertEquals('ircAccount', $account->getAccountName());
	}

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
		$incoming = new IncomingIrcMessage($line);
		$authenticate = AUTHENTICATE::fromIncomingIrcMessage($incoming);

		static::assertEquals('+', $authenticate->getResponse());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		AUTHENTICATE::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname AWAY :A sample message' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$away = AWAY::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $away->getNickname());
		static::assertEquals('A sample message', $away->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		AWAY::fromIncomingIrcMessage($incomingIrcMessage);
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
        $line = Parser::parseLine(':server CAP * LS :cap1 cap2' . "\r\n");
        $incoming = new IncomingIrcMessage($line);
        $cap = CAP::fromIncomingIrcMessage($incoming);

        static::assertEquals('LS', $cap->getCommand());
        static::assertEquals(['cap1', 'cap2'], $cap->getCapabilities());
        static::assertEquals('*', $cap->getNickname());

	    $message = ':server TEEHEE argument' . "\r\n";
	    $parsedLine = Parser::parseLine($message);
	    $incomingIrcMessage = new IncomingIrcMessage($parsedLine);
	    $this->expectException(\InvalidArgumentException::class);
	    CAP::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testErrorReceive()
	{
		$line = Parser::parseLine('ERROR :A sample message' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$error = ERROR::fromIncomingIrcMessage($incoming);

		static::assertEquals('A sample message', $error->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		ERROR::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname JOIN #channel ircAccountName :realname' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$join = JOIN::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $join->getNickname());
		static::assertEquals(['#channel'], $join->getChannels());
		static::assertEquals('ircAccountName', $join->getIrcAccount());
		static::assertEquals('realname', $join->getRealname());
	}

	public function testJoinReceiveRegular()
	{
		$line = Parser::parseLine(':nickname!username@hostname JOIN #channel' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$join = JOIN::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $join->getNickname());
		static::assertEquals(['#channel'], $join->getChannels());
		static::assertEquals('', $join->getIrcAccount());
		static::assertEquals('', $join->getRealname());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		JOIN::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname KICK #somechannel othernickname :You deserved it!');
		$incoming = new IncomingIrcMessage($line);
		$kick = KICK::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $kick->getPrefix());
		static::assertEquals('nickname', $kick->getNickname());
		static::assertEquals('othernickname', $kick->getTarget());
		static::assertEquals('#somechannel', $kick->getChannel());
		static::assertEquals('You deserved it!', $kick->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		KICK::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname MODE #channel -o+b arg1 arg2' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$mode = MODE::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $mode->getPrefix());
		static::assertEquals('#channel', $mode->getTarget());
		static::assertEquals('nickname', $mode->getNickname());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals(['arg1', 'arg2'], $mode->getArguments());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		MODE::fromIncomingIrcMessage($incomingIrcMessage);
	}

	public function testModeReceiveUser()
	{
		$line = Parser::parseLine(':nickname!username@hostname MODE user -o+b' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$mode = MODE::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $mode->getPrefix());
		static::assertEquals('user', $mode->getTarget());
		static::assertEquals('nickname', $mode->getNickname());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals([], $mode->getArguments());
	}

	public function testModeReceiveInitial()
	{
		$line = Parser::parseLine(':nickname!username@hostname MODE nickname -o+b' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$mode = MODE::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $mode->getTarget());
		static::assertEquals('nickname', $mode->getNickname());
		static::assertEquals('-o+b', $mode->getFlags());
		static::assertEquals([], $mode->getArguments());
	}

	public function testNamesCreate()
	{
		$names = new NAMES('#testChannel', 'testServer');

		static::assertEquals(['#testChannel'], $names->getChannels());
		static::assertEquals('testServer', $names->getServer());

		$expected = 'NAMES #testChannel testServer';
		static::assertEquals($expected, $names->__toString());
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
		$line = Parser::parseLine(':nickname!username@hostname NICK newnickname' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$nick = NICK::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $nick->getPrefix());
		static::assertEquals('nickname', $nick->getNickname());
		static::assertEquals('newnickname', $nick->getNewNickname());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		NICK::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname NOTICE #somechannel :This is a test message' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$notice = NOTICE::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $notice->getPrefix());
		static::assertEquals('#somechannel', $notice->getChannel());
		static::assertEquals('This is a test message', $notice->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		NOTICE::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname PART #channel :I have a valid reason' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$part = PART::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $part->getPrefix());
		static::assertEquals('nickname', $part->getNickname());
		static::assertEquals(['#channel'], $part->getChannels());
		static::assertEquals('I have a valid reason', $part->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		PART::fromIncomingIrcMessage($incomingIrcMessage);
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
		$incoming = new IncomingIrcMessage($line);
		$ping = PING::fromIncomingIrcMessage($incoming);

		static::assertEquals('testserver1', $ping->getServer1());
		static::assertEquals('testserver2', $ping->getServer2());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		PING::fromIncomingIrcMessage($incomingIrcMessage);
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
		$incoming = new IncomingIrcMessage($line);
		$pong = PONG::fromIncomingIrcMessage($incoming);

		static::assertEquals('testserver1', $pong->getServer1());
		static::assertEquals('testserver2', $pong->getServer2());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		PONG::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname PRIVMSG #somechannel :This is a test message' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$privmsg = PRIVMSG::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $privmsg->getPrefix());
		static::assertEquals('#somechannel', $privmsg->getChannel());
		static::assertEquals('This is a test message', $privmsg->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		PRIVMSG::fromIncomingIrcMessage($incomingIrcMessage);
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
		$line = Parser::parseLine(':nickname!username@hostname QUIT :A sample message' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$quit = QUIT::fromIncomingIrcMessage($incoming);

		$userPrefix = new UserPrefix('nickname', 'username', 'hostname');
		static::assertEquals($userPrefix, $quit->getPrefix());
		static::assertEquals('nickname', $quit->getNickname());
		static::assertEquals('A sample message', $quit->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		QUIT::fromIncomingIrcMessage($incomingIrcMessage);
	}

	public function testRawCreate()
    {
        $raw = new RAW('a command');

        static::assertEquals('a command', $raw->getCommand());

        $expected = 'a command' . "\r\n";
        static::assertEquals($expected, $raw->__toString());
    }

	public function testRplEndOfNamesReceive()
	{
		$line = Parser::parseLine(':server 366 nickname #channel :End of /NAMES list.' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$rpl_endofnames = RPL_ENDOFNAMES::fromIncomingIrcMessage($incoming);

		static::assertEquals('nickname', $rpl_endofnames->getNickname());
		static::assertEquals('#channel', $rpl_endofnames->getChannel());
		static::assertEquals('End of /NAMES list.', $rpl_endofnames->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		RPL_ENDOFNAMES::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testRplIsupportReceive()
	{
		$line = Parser::parseLine(':server 005 nickname KEY1=value KEY2=value2 :are supported by this server' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$rpl_isupport = RPL_ISUPPORT::fromIncomingIrcMessage($incoming);

		static::assertEquals(['key1' => 'value', 'key2' => 'value2'], $rpl_isupport->getVariables());
		static::assertEquals('server', $rpl_isupport->getServer());
		static::assertEquals('nickname', $rpl_isupport->getNickname());
		static::assertEquals('are supported by this server', $rpl_isupport->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		RPL_ISUPPORT::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testRplNamReplyReceive()
	{
		$line = Parser::parseLine(':server 353 nickname + #channel :nickname1 nickname2 nickname3' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$rpl_namreply = RPL_NAMREPLY::fromIncomingIrcMessage($incoming);

		static::assertEquals('server', $rpl_namreply->getServer());
		static::assertEquals('nickname', $rpl_namreply->getNickname());
		static::assertEquals('+', $rpl_namreply->getVisibility());
		static::assertEquals(['nickname1', 'nickname2', 'nickname3'], $rpl_namreply->getNicknames());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		RPL_NAMREPLY::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testRplTopicReceive()
	{
		$line = Parser::parseLine(':server 332 nickname #channel :A new topic message' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$rpl_topic = RPL_TOPIC::fromIncomingIrcMessage($incoming);

		static::assertEquals('server', $rpl_topic->getServer());
		static::assertEquals('nickname', $rpl_topic->getNickname());
		static::assertEquals('#channel', $rpl_topic->getChannel());
		static::assertEquals('A new topic message', $rpl_topic->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		RPL_TOPIC::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testRplWelcomeReceive()
	{
		$line = Parser::parseLine(':server 001 nickname :Welcome to server!' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$rpl_welcome = RPL_WELCOME::fromIncomingIrcMessage($incoming);

		static::assertEquals('server', $rpl_welcome->getServer());
		static::assertEquals('nickname', $rpl_welcome->getNickname());
		static::assertEquals('Welcome to server!', $rpl_welcome->getMessage());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		RPL_WELCOME::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testRplWhosPCRplReceive()
	{
		$line = Parser::parseLine(':server 354 ownnickname username hostname nickname status accountname' . "\r\n");
		$incoming = new IncomingIrcMessage($line);
		$rpl_whospcrpl = RPL_WHOSPCRPL::fromIncomingIrcMessage($incoming);

		static::assertEquals('server', $rpl_whospcrpl->getServer());
		static::assertEquals('ownnickname', $rpl_whospcrpl->getOwnNickname());
		static::assertEquals('username', $rpl_whospcrpl->getUsername());
		static::assertEquals('hostname', $rpl_whospcrpl->getHostname());
		static::assertEquals('nickname', $rpl_whospcrpl->getNickname());
		static::assertEquals('status', $rpl_whospcrpl->getStatus());
		static::assertEquals('accountname', $rpl_whospcrpl->getAccountname());

		$message = ':server TEEHEE argument' . "\r\n";
		$parsedLine = Parser::parseLine($message);
		$incomingIrcMessage = new IncomingIrcMessage($parsedLine);
		$this->expectException(\InvalidArgumentException::class);
		RPL_WHOSPCRPL::fromIncomingIrcMessage($incomingIrcMessage);
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
        $line = Parser::parseLine(':nickname!username@hostname TOPIC #someChannel :This is a new topic' . "\r\n");
        $incoming = new IncomingIrcMessage($line);
        $topic = TOPIC::fromIncomingIrcMessage($incoming);

	    $userPrefix = new UserPrefix('nickname', 'username', 'hostname');
	    static::assertEquals($userPrefix, $topic->getPrefix());
        static::assertEquals('#someChannel', $topic->getChannel());
        static::assertEquals('This is a new topic', $topic->getMessage());

	    $message = ':server TEEHEE argument' . "\r\n";
	    $parsedLine = Parser::parseLine($message);
	    $incomingIrcMessage = new IncomingIrcMessage($parsedLine);
	    $this->expectException(\InvalidArgumentException::class);
	    TOPIC::fromIncomingIrcMessage($incomingIrcMessage);
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
        $incoming = new IncomingIrcMessage($line);
        $user = USER::fromIncomingIrcMessage($incoming);

        static::assertEquals('myusername', $user->getUsername());
        static::assertEquals('localhost', $user->getHostname());
        static::assertEquals('someserver', $user->getServername());
        static::assertEquals('arealname', $user->getRealname());

	    $message = ':server TEEHEE argument' . "\r\n";
	    $parsedLine = Parser::parseLine($message);
	    $incomingIrcMessage = new IncomingIrcMessage($parsedLine);
	    $this->expectException(\InvalidArgumentException::class);
	    USER::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testVersionCreate()
	{
		$version = new VERSION('server');
		static::assertEquals('server', $version->getServer());

		$expected = 'VERSION server';
		static::assertEquals($expected, $version->__toString());

		$version = new VERSION();
		$expected = 'VERSION';
		static::assertEquals($expected, $version->__toString());
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
        $line = Parser::parseLine(':nickname!username@hostname WHO #someChannel %nuhaf' . "\r\n");
        $incoming = new IncomingIrcMessage($line);
        $who = WHO::fromIncomingIrcMessage($incoming);

	    $userPrefix = new UserPrefix('nickname', 'username', 'hostname');
	    static::assertEquals($userPrefix, $who->getPrefix());
        static::assertEquals('#someChannel', $who->getChannel());
        static::assertEquals('%nuhaf', $who->getOptions());

	    $message = ':server TEEHEE argument' . "\r\n";
	    $parsedLine = Parser::parseLine($message);
	    $incomingIrcMessage = new IncomingIrcMessage($parsedLine);
	    $this->expectException(\InvalidArgumentException::class);
	    WHO::fromIncomingIrcMessage($incomingIrcMessage);
    }

	public function testWhoisCreate()
	{
		$whois = new WHOIS(['nickname1', 'nickname2'], 'server');
		static::assertEquals(['nickname1', 'nickname2'], $whois->getNicknames());
		static::assertEquals('server', $whois->getServer());

		$expected = 'WHOIS server nickname1,nickname2';
		static::assertEquals($expected, $whois->__toString());
    }

	public function testWhoWasCreate()
	{
		$whowas = new WHOWAS(['nickname1', 'nickname2'], 2, 'server');
		static::assertEquals(['nickname1', 'nickname2'], $whowas->getNicknames());
		static::assertEquals(2, $whowas->getCount());
		static::assertEquals('server', $whowas->getServer());

		$expected = 'WHOWAS nickname1,nickname2 2 server';
		static::assertEquals($expected, $whowas->__toString());
	}
}
