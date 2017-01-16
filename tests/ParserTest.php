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
	public function testSpecializePrivmsg()
	{
		$lineToTest = ':Nickname!~IRCAccount@some/random/hostname PRIVMSG #channel :A random message!' . "\r\n";

		$parsedLine = Parser::parseLine($lineToTest);
		$message = new IncomingIrcMessage($parsedLine);
		$message = $message->specialize();

		static::assertInstanceOf('\WildPHP\Core\Connection\IncomingIrcMessages\PRIVMSG', $message);
	}
}
