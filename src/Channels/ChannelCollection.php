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
	protected $collection = [];
	
	public function addChannel(Channel $channel)
	{
		if ($this->channelExists($channel) || $this->channelExistsByName($channel->getName()))
		{
			Logger::warning('Trying to add existing channel to collection', [$channel]);
			return;
		}

		$this->collection[$channel->getName()] = $channel;
	}

	public function removeChannel(Channel $channel)
	{
		if (!$this->channelExists($channel))
		{
			Logger::warning('Trying to remove non-existing channel from collection', [$channel]);
			return;
		}

		unset($this->collection[array_search($channel->getName(), $this->collection)]);
	}

	public function channelExists(Channel $channel)
	{
		return in_array($channel, $this->collection);
	}

	public function channelExistsByName(string $name)
	{
		return array_key_exists($name, $this->collection);
	}

	public function getChannelByName(string $name): Channel
	{
		// TODO
		return $this->collection[$name];
	}
}