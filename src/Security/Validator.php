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

namespace WildPHP\Core\Security;

use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Users\User;

class Validator
{
	/**
	 * @var ComponentContainer
	 */
	protected $container;

	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);
	}

	/**
	 * @param Channel $channel
	 * @param User $user
	 * @return bool
	 */
	public function isUserOPInChannel(Channel $channel, User $user)
	{
		return $channel->getChannelModes()->isUserInMode('o', $user);
	}

	/**
	 * @param Channel $channel
	 * @param User $user
	 * @return bool
	 */
	public function isUserVoicedInChannel(Channel $channel, User $user)
	{
		return $channel->getChannelModes()->isUserInMode('v', $user);
	}

	/**
	 * @param string $permissionName
	 * @param User $user
	 * @param Channel|null $channel
	 *
	 * @return string|boolean String with reason on success; boolean false otherwise.
	 */
	public function isAllowedTo(string $permissionName = '', User $user, Channel $channel = null)
	{
		// The order to check in:
		// 0. Is bot owner (has all perms)
		// 1. User OP in channel
		// 2. User Voice in channel
		// 3. User in other group with permission
		if ($user->getIrcAccount() == $this->getContainer()->getConfiguration()->get('owner')->getValue())
			return 'owner';

		if (!empty($channel) && self::isUserOPInChannel($channel, $user))
		{
			$opGroup = $this->getContainer()->getPermissionGroupCollection()->findGroupByName('op');

			if ($opGroup->hasPermission($permissionName))
				return 'op';
		}

		if (!empty($channel) && self::isUserVoicedInChannel($channel, $user))
		{
			$voiceGroup = $this->getContainer()->getPermissionGroupCollection()->findGroupByName('voice');

			if ($voiceGroup->hasPermission($permissionName))
				return 'voice';
		}

		$groups = $this->getContainer()->getPermissionGroupCollection()->findAll(function ($item) use ($user)
		{
			if (!$item->getCanHaveMembers())
				return false;

			return $item->isMember($user);
		});

		foreach ($groups->toArray() as $group)
		{
			if ($group->hasPermission($permissionName))
				return $group->getName();
		}

		return false;
	}

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}
}