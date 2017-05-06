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


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\EventEmitter;

class Parser
{
	/**
	 * Parser constructor.
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		EventEmitter::fromContainer($container)
			->on('stream.line.in',
				function ($line) use ($container)
				{
					$parsedLine = self::parseLine($line);
					$ircMessage = new IncomingIrcMessage($parsedLine, $container);

					$verb = strtolower($ircMessage->getVerb());
					EventEmitter::fromContainer($container)
						->emit('irc.line.in', [$ircMessage, Queue::fromContainer($container)]);

					$ircMessage = $ircMessage->specialize();
					EventEmitter::fromContainer($container)
						->emit('irc.line.in.' . $verb, [$ircMessage, Queue::fromContainer($container)]);
				});
	}

	/**
	 * @param string $line
	 *
	 * @return ParsedIrcMessageLine
	 */
	public static function parseLine(string $line): ParsedIrcMessageLine
	{
		$parsed = ParsedIrcMessageLine::parse($line);

		return $parsed;
	}
}