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
use WildPHP\Core\Channels\GlobalChannelCollection;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IncomingIrcMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Users\GlobalUserCollection;

class CommandHandler
{
	/**
	 * @var string
	 */
	protected static $prefix = '!';

	public static function initialize()
	{
		GlobalCommandDictionary::setDictionary(new Dictionary());

		EventEmitter::on('irc.line.in.privmsg', __CLASS__ . '::parseAndRunCommand');

		self::setPrefix(Configuration::get('prefix')->getValue());
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function parseAndRunCommand(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$privmsg = PRIVMSG::fromIncomingIrcMessage($incomingIrcMessage);
		$source = $privmsg->getChannel();
		$message = $privmsg->getMessage();
		$user = $privmsg->getUser();

		$args = [];
		$command = self::parseCommandFromMessage($message, $args);

		if ($command === false)
			return;

		$dictionary = GlobalCommandDictionary::getDictionary();

		if (!$dictionary->keyExists($command))
			return;

		call_user_func($dictionary[$command], $source, $user, $args, $queue);
	}

	/**
	 * @param string $message
	 * @param array $args
	 *
	 * @return false|string
	 */
	protected static function parseCommandFromMessage(string $message, array &$args)
	{
		$messageParts = explode(' ', $message);
		$firstPart = $messageParts[0];

		if (strlen($firstPart) == strlen(self::getPrefix()))
			return false;

		if (substr($firstPart, 0, strlen(self::getPrefix())) != self::getPrefix())
			return false;

		$command = substr($firstPart, strlen(self::getPrefix()));
		array_shift($messageParts);
		$args = $messageParts;

		return $command;
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
}