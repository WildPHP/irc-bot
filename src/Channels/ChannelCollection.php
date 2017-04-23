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
use WildPHP\Core\Users\GlobalUserCollection;
use WildPHP\Core\Users\User;

class ChannelCollection
{
	/**
	 * @var Channel[]
	 */
	protected $collection = [];

	/**
	 * @param Channel $channel
	 */
	public function addChannel(Channel $channel)
	{
		if (self::channelExists($channel) || self::channelExistsByName($channel->getName()))
		{
			Logger::warning('Trying to add existing channel to collection', [$channel]);

			return;
		}

		$this->collection[$channel->getName()] = $channel;
	}

	/**
	 * Creates a fake channel with the bot and another user in it, to allow private conversations to happen.
	 *
	 * @param User $user
	 * @return Channel
	 */
	public function createFakeConversationChannel(User $user)
	{
		$channel = new Channel();
		$channel->updateParticipatingUsers($user, $user->getNickname());
		$channel->updateParticipatingUsers(GlobalUserCollection::getSelf(), $user->getNickname());
		$channel->setName($user->getNickname());
		$this->addChannel($channel);
		return $channel;
	}

	/**
	 * @param Channel $channel
	 */
	public function removeChannel(Channel $channel)
	{
		if (!self::channelExists($channel))
		{
			Logger::warning('Trying to remove non-existing channel from collection', [$channel]);

			return;
		}

		unset($this->collection[array_search($channel->getName(), $this->collection)]);
	}

	/**
	 * @param Channel $channel
	 *
	 * @return bool
	 */
	public function channelExists(Channel $channel): bool
	{
		return in_array($channel, $this->collection);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function channelExistsByName(string $name): bool
	{
		return array_key_exists($name, $this->collection);
	}

	/**
	 * @param string $name
	 *
	 * @return Channel
	 */
	public function getChannelByName(string $name): Channel
	{
		// TODO
		return $this->collection[$name];
	}

	/**
	 * @return array
	 */
	public function getAllChannels(): array
	{
		return $this->collection;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->collection);
	}
}