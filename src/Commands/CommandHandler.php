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

namespace WildPHP\Core\Commands;


use Collections\Dictionary;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

class CommandHandler
{
	/**
	 * @var string
	 */
	protected static $prefix = '!';
	
	public static function initialize()
	{
		GlobalCommandDictionary::setDictionary(new Dictionary());

		CommandRegistrar::registerCommand('ping', __CLASS__ . '::pingPong');
		EventEmitter::on('irc.line.in.privmsg', __CLASS__ . '::tryParseCommand');
		
		self::setPrefix(Configuration::get('prefix')->getValue());
	}

	public static function tryParseCommand(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		Logger::debug('Incoming message', [$incomingIrcMessage]);
		$args = $incomingIrcMessage->getArgs();
		$source = $args[0];
		$message = $args[1];

		$messageParts = explode(' ', $message);
		$firstPart = $messageParts[0];
		
		if (substr($firstPart, 0, strlen(self::getPrefix())) != self::getPrefix())
			return;
		
		$command = substr($firstPart, strlen(self::getPrefix()));
		array_shift($messageParts);
		$args = $messageParts;

		$dictionary = GlobalCommandDictionary::getDictionary();

		if (!$dictionary->keyExists($command))
			return;

		call_user_func($dictionary[$command], $source, $args, $queue);
	}

	/**
	 * @return string
	 */
	public static function getPrefix()
	{
		return self::$prefix;
	}

	/**
	 * @param string $prefix
	 */
	public static function setPrefix($prefix)
	{
		self::$prefix = $prefix;
	}

	public static function pingPong(string $source, array $args, Queue $queue)
	{
		$queue->privmsg($source, 'Pong!');
	}
}