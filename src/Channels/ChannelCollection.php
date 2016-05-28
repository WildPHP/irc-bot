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


use WildPHP\Core\Logger\Logger;

class ChannelCollection
{
	/**
	 * @var Channel[]
	 */
	protected static $collection = [];
	
	public static function addChannel(Channel $channel)
	{
		if (self::channelExists($channel) || self::channelExistsByName($channel->getName()))
		{
			Logger::warning('Trying to add existing channel to collection', [$channel]);
			return;
		}

		self::$collection[$channel->getName()] = $channel;
	}

	public static function removeChannel(Channel $channel)
	{
		if (!self::channelExists($channel))
		{
			Logger::warning('Trying to remove non-existing channel from collection', [$channel]);
			return;
		}

		unset(self::$collection[array_search($channel->getName(), self::$collection)]);
	}

	public static function channelExists(Channel $channel)
	{
		return in_array($channel, self::$collection);
	}

	public static function channelExistsByName(string $name)
	{
		return array_key_exists($name, self::$collection);
	}

	public static function getChannelByName(string $name): Channel
	{
		// TODO
		return self::$collection[$name];
	}
}