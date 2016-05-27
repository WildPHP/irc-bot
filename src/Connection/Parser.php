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

namespace WildPHP\Core\Connection;

use WildPHP\Core\Events\EventEmitter;

class Parser
{
	public static function initialize(Queue $queue)
	{
		EventEmitter::on('stream.line.in', function ($line) use ($queue)
		{
			$parsedLine = self::parseLine($line);
			$ircMessage = new IncomingIrcMessage($parsedLine);

			$verb = strtolower($ircMessage->getVerb());
			EventEmitter::emit('irc.line.in', [$ircMessage, $queue]);
			EventEmitter::emit('irc.line.in.' . $verb, [$ircMessage, $queue]);
		});
	}

	public static function parseLine(string $line): ParsedIrcMessageLine
	{
		$parsed = ParsedIrcMessageLine::parse($line);

		return $parsed;
	}
}