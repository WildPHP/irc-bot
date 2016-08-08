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

namespace WildPHP\Core\Connection\IncomingIrcMessages;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\GlobalChannelCollection;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;
use WildPHP\Core\Users\GlobalUserCollection;
use WildPHP\Core\Users\User;

class JOIN
{
	/**
	 * @var UserPrefix
	 */
	protected $prefix = null;

	/**
	 * @var Channel[]
	 */
	protected $channels = [];

	/**
	 * @var User
	 */
	protected $user = null;

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return JOIN
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage)
	{
		if ($incomingIrcMessage->getVerb() != 'JOIN')
			throw new \InvalidArgumentException('Expected incoming JOIN; got ' . $incomingIrcMessage->getVerb());

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$channelNameList = explode(',', $incomingIrcMessage->getArgs()[0]);
		$channels = self::getChannelsByList($channelNameList);
		$user = GlobalUserCollection::getUserFromIncomingIrcMessage($incomingIrcMessage);

		$object = new self();

		$object->setPrefix($prefix);
		$object->setUser($user);
		$object->setChannels($channels);
		return $object;
	}

	/**
	 * @param array $channelNames
	 *
	 * @return array
	 */
	protected static function getChannelsByList(array $channelNames): array
	{
		$channelObjects = [];
		foreach ($channelNames as $channelName)
		{
			$channel = GlobalChannelCollection::getChannelCollection()->getChannelByName($channelName);
			$channelObjects[] = $channel;
		}
		return $channelObjects;
	}

	/**
	 * @return UserPrefix
	 */
	public function getPrefix(): UserPrefix
	{
		return $this->prefix;
	}

	/**
	 * @param UserPrefix $prefix
	 */
	public function setPrefix(UserPrefix $prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return Channel[]
	 */
	public function getChannels(): array
	{
		return $this->channels;
	}

	/**
	 * @param Channel[] $channels
	 */
	public function setChannels(array $channels)
	{
		$this->channels = $channels;
	}

	/**
	 * @return User
	 */
	public function getUser(): User
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}
}