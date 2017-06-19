<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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

	/**
	 * @param PRIVMSG $incoming
	 * @param Queue $queue
	 */
	public function prettifyPrivmsg(PRIVMSG $incoming, Queue $queue)
	{
		$nickname = $incoming->getNickname();
		$channel = $incoming->getChannel();
		$message = $incoming->getMessage();

		$toLog = 'INC: [' . $channel . '] <' . $nickname . '> ' . $message;

		Logger::fromContainer($this->getContainer())
			->info($toLog);
	}

	/**
	 * @param QueueItem $message
	 * @param ComponentContainer $container
	 */
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