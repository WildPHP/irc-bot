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

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;

class Parser
{
	use ContainerTrait;

	/**
	 * Parser constructor.
	 *
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

		EventEmitter::fromContainer($container)
			->on('irc.line.in.privmsg', [$this, 'prettifyPrivmsg']);
		EventEmitter::fromContainer($container)
			->on('irc.line.out', [$this, 'prettifyOutgoingPrivmsg']);
		$this->setContainer($container);
	}

	public function prettifyPrivmsg(PRIVMSG $incoming, Queue $queue)
	{
		$nickname = $incoming->getNickname();
		$channel = $incoming->getChannel();
		$message = $incoming->getMessage();

		$toLog = 'INC: [' . $channel . '] <' . $nickname . '> ' . $message;

		Logger::fromContainer($this->getContainer())
			->info($toLog);
	}

	public function prettifyOutgoingPrivmsg(QueueItem $message, ComponentContainer $container)
	{
		$message = $message->getCommandObject();
		if (!($message instanceof PRIVMSG))
			return;

		$channel = $message->getChannel();
		$message = $message->getMessage();

		$toLog = 'OUT: [' . $channel . '] ' . $message;
		Logger::fromContainer($container)
			->info($toLog);
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