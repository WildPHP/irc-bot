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

namespace WildPHP\Core\Users;


use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;

class UserDataCollector
{
	/**
	 * @var UserCollection
	 */
	protected static $userGroup = null;
	
	public static function initialize()
	{
		self::$userGroup = new UserCollection();
		
		EventEmitter::on('irc.line.in.366', __NAMESPACE__ . '\UserDataCollector::sendWhox');
		EventEmitter::on('irc.line.in.354', __NAMESPACE__ . '\UserDataCollector::processWhox');
	}

	public static function sendWhox(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$channel = $incomingIrcMessage->getArgs()[1];
		$queue->who($channel, '%na');
	}

	public static function processWhox(IncomingIrcMessage $incomingIrcMessage)
	{
		$args = $incomingIrcMessage->getArgs();
		$nickname = $args[1];
		$accountname = $args[2];
		
		
	}
}