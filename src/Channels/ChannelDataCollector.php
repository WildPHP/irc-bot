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

namespace WildPHP\Core\Channels;

use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

class ChannelDataCollector
{
	/**
	 * @var ChannelCollection
	 */
	protected static $channelCollection;

	/**
	 * Stored as 'prefix' => 'mode'
	 * @var array
	 */
	protected static $modeMap = [];

	public static function initialize()
	{
		self::$channelCollection = new ChannelCollection();
		EventEmitter::on('irc.line.in.332', __NAMESPACE__ . '\ChannelDataCollector::updateChannelTopic');
		EventEmitter::on('irc.line.in.353', __NAMESPACE__ . '\ChannelDataCollector::createUserStructure');
	}

	public static function createUserStructure(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		Logger::debug('Got NAMES list reply', [$incomingIrcMessage]);
		$args = $incomingIrcMessage->getArgs();
		$channel = $args[2];
		$users = explode(' ', $args[3]);

		if (!self::$channelCollection->channelExistsByName($channel))
			$channelData = self::addNewChannelByName($channel);
		else
			$channelData = self::$channelCollection->getChannelByName($channel);

		if (empty(self::$modeMap))
			self::createModeMap();

		foreach ($users as $user)
		{
			
			$firstChar = substr($user, 0, 1);
			if (array_key_exists($firstChar, self::$modeMap))
			{
				
			}
		}
	}

	public static function createModeMap()
	{
		$availablemodes = Configuration::get('serverConfig.prefix')->getValue();

		preg_match('/\((.+)\)(.+)/', $availablemodes, $out);

		$modes = str_split($out[1]);
		$prefixes = str_split($out[2]);
		self::$modeMap = array_combine($prefixes, $modes);

		Logger::debug('Set new mode map', ['map' => self::$modeMap]);
	}
	
	public static function updateChannelTopic(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$channel = $incomingIrcMessage->getArgs()[1];
		$topic = $incomingIrcMessage->getArgs()[2];
		Logger::debug('New topic set', ['topic' => $topic, 'channel' => $channel]);
		
		if (!self::$channelCollection->channelExistsByName($channel))
			self::addNewChannelByName($channel);

		$channel = self::$channelCollection->getChannelByName($channel);
		$channel->setTopic($topic);
	}

	public static function addNewChannelByName(string $name): Channel
	{
		$channel = new Channel();
		$channel->setName($name);
		self::$channelCollection->addChannel($channel);
		return $channel;
	}
}