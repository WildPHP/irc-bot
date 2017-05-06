<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 20:17
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IRCMessages\AUTHENTICATE;
use WildPHP\Core\Connection\IRCMessages\AWAY;
use WildPHP\Core\Connection\IRCMessages\ERROR;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\MODE;
use WildPHP\Core\Connection\IRCMessages\NICK;
use WildPHP\Core\Connection\Parser;

class IrcMessageTest extends TestCase
{
	public function testAuthenticateCreate()
	{
		$authenticate = new AUTHENTICATE('+');

		$this->assertEquals('+', $authenticate->getResponse());

		$expected = 'AUTHENTICATE +' . "\r\n";
		$this->assertEquals($expected, $authenticate->__toString());
	}

	public function testAuthenticateReceive()
	{
		$line = Parser::parseLine('AUTHENTICATE +' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$authenticate = AUTHENTICATE::fromIncomingIrcMessage($incoming);

		$this->assertEquals('+', $authenticate->getResponse());
	}

	public function testAwayCreate()
	{
		$away = new AWAY('A sample message');

		$this->assertEquals('A sample message', $away->getMessage());

		$expected = 'AWAY :A sample message' . "\r\n";
		$this->assertEquals($expected, $away->__toString());
	}

	public function testAwayReceive()
	{

	}

	public function testErrorCreate()
	{
		$error = new ERROR('Test error message');

		$this->assertEquals('Test error message', $error->getMessage());

		$expected = 'ERROR :Test error message' . "\r\n";
		$this->assertEquals($expected, $error->__toString());
	}

	public function testErrorReceive()
	{

	}

	public function testJoinCreate()
	{
		$join = new JOIN(['#channel1', '#channel2'], ['key1', 'key2']);

		$this->assertEquals(['#channel1', '#channel2'], $join->getChannels());
		$this->assertEquals(['key1', 'key2'], $join->getKeys());

		$expected = 'JOIN #channel1,#channel2 key1,key2' . "\r\n";
		$this->assertEquals($expected, $join->__toString());
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

		$this->assertEquals('nickname', $join->getNickname());
		$this->assertEquals(['#channel'], $join->getChannels());
		$this->assertEquals('ircAccountName', $join->getIrcAccount());
		$this->assertEquals('realname', $join->getRealname());
	}

	public function testJoinReceiveRegular()
	{
		$line = Parser::parseLine(':nickname!host JOIN #channel' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$join = JOIN::fromIncomingIrcMessage($incoming);

		$this->assertEquals('nickname', $join->getNickname());
		$this->assertEquals(['#channel'], $join->getChannels());
		$this->assertEquals('', $join->getIrcAccount());
		$this->assertEquals('', $join->getRealname());
	}

	public function testKickCreate()
	{
		$kick = new KICK('#channel', 'nickname', 'Bleep you!');

		$this->assertEquals('#channel', $kick->getChannel());
		$this->assertEquals('nickname', $kick->getTarget());
		$this->assertEquals('Bleep you!', $kick->getMessage());

		$expected = 'KICK #channel nickname :Bleep you!' . "\r\n";
		$this->assertEquals($expected, $kick->__toString());
	}

	public function testKickReceive()
	{

	}

	public function testModeCreate()
	{
		$mode = new MODE('target', '-o+b', ['arg1', 'arg2']);

		$this->assertEquals('target', $mode->getTarget());
		$this->assertEquals('-o+b', $mode->getFlags());
		$this->assertEquals(['arg1', 'arg2'], $mode->getArguments());

		$expected = 'MODE target -o+b arg1 arg2' . "\r\n";
		$this->assertEquals($expected, $mode->__toString());
	}

	public function testModeReceiveChannel()
	{
		$line = Parser::parseLine(':nickname!host MODE #channel -o+b arg1 arg2' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$mode = MODE::fromIncomingIrcMessage($incoming);

		$this->assertEquals('#channel', $mode->getTarget());
		$this->assertEquals('nickname', $mode->getNickname());
		$this->assertEquals('-o+b', $mode->getFlags());
		$this->assertEquals(['arg1', 'arg2'], $mode->getArguments());
	}

	public function testModeReceiveUser()
	{
		$line = Parser::parseLine(':nickname!host MODE user -o+b' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$mode = MODE::fromIncomingIrcMessage($incoming);

		$this->assertEquals('user', $mode->getTarget());
		$this->assertEquals('nickname', $mode->getNickname());
		$this->assertEquals('-o+b', $mode->getFlags());
		$this->assertEquals([], $mode->getArguments());
	}

	public function testModeReceiveInitial()
	{
		$line = Parser::parseLine(':nickname MODE nickname -o+b' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$mode = MODE::fromIncomingIrcMessage($incoming);

		$this->assertEquals('nickname', $mode->getTarget());
		$this->assertEquals('nickname', $mode->getNickname());
		$this->assertEquals('-o+b', $mode->getFlags());
		$this->assertEquals([], $mode->getArguments());
	}

	public function testNickCreate()
	{
		$nick = new NICK('newnickname');

		$this->assertEquals('newnickname', $nick->getNewNickname());

		$expected = 'NICK newnickname' . "\r\n";
		$this->assertEquals($expected, $nick->__toString());
	}

	public function testNickReceive()
	{
		$line = Parser::parseLine(':nickname!host NICK newnickname' . "\r\n");
		$incoming = new IncomingIrcMessage($line, new ComponentContainer());
		$nick = NICK::fromIncomingIrcMessage($incoming);
	}

	public function testNoticeCreate()
	{

	}

	public function testNoticeReceive()
	{

	}

	public function testPartCreate()
	{

	}

	public function testPartReceive()
	{

	}

	public function testPingCreate()
	{

	}

	public function testPingReceive()
	{

	}

	public function testPongCreate()
	{

	}

	public function testPongReceive()
	{

	}

	public function testPrivmsgCreate()
	{

	}

	public function testPrivmsgReceive()
	{

	}

	public function testQuitCreate()
	{

	}

	public function testQuitReceive()
	{

	}
}
