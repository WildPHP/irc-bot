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

use Collections\Collection;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class ChannelCollection extends Collection
{
	protected static $globalInstance = null;

	/**
	 * @return ChannelCollection
	 */
	public static function getGlobalInstance(): ChannelCollection
	{
		if (is_null(self::$globalInstance))
			self::$globalInstance = new ChannelCollection();

		return self::$globalInstance;
	}

	public function __construct()
	{
		parent::__construct('\WildPHP\Core\Channels\Channel');
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
		$channel->setName($user->getNickname());
		$channel->getUserCollection()->add($user);
		$channel->getUserCollection()->add(UserCollection::getGlobalSelf());
		$this->add($channel);
		return $channel;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function containsChannelName(string $name): bool
	{
		return !empty($this->findByChannelName($name));
	}

	/**
	 * @param string $name
	 * @return false|Channel
	 */
	public function findByChannelName(string $name)
	{
		return $this->find(function (Channel $channel) use ($name)
		{
			return $channel->getName() == $name;
		});
	}
}