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

use Flintstone\Config;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Users\User;

class Validator
{
	/**
	 * @param Channel $channel
	 * @param User $user
	 * @return bool
	 */
	public static function isUserOPInChannel(Channel $channel, User $user)
	{
		return $channel->isUserInMode('o', $user);
	}

	/**
	 * @param Channel $channel
	 * @param User $user
	 * @return bool
	 */
	public static function isUserVoicedInChannel(Channel $channel, User $user)
	{
		return $channel->isUserInMode('v', $user);
	}

	/**
	 * @param string $permissionName
	 * @param User $user
	 * @param Channel|null $channel
	 *
	 * @return string|boolean String with reason on success; boolean false otherwise.
	 */
	public static function isAllowedTo(string $permissionName = '', User $user, Channel $channel = null)
	{
		// The order to check in:
		// 0. Is bot owner (has all perms)
		// 1. User OP in channel
		// 2. User Voice in channel
		// 3. User in other group with permission
		if ($user->getIrcAccount() == Configuration::get('owner')->getValue())
			return 'owner';

		if (!empty($channel) && self::isUserOPInChannel($channel, $user))
		{
			$opGroup = GlobalPermissionGroupCollection::getPermissionGroupCollection()->findGroupByName('op');

			if ($opGroup->hasPermission($permissionName))
				return 'op';
		}

		if (!empty($channel) && self::isUserVoicedInChannel($channel, $user))
		{
			$voiceGroup = GlobalPermissionGroupCollection::getPermissionGroupCollection()->findGroupByName('voice');

			if ($voiceGroup->hasPermission($permissionName))
				return 'voice';
		}

		$groups = GlobalPermissionGroupCollection::getPermissionGroupCollection()->findall(function ($item) use ($user)
		{
			if (!$item->getCanHaveMembers())
				return false;

			if ($item->isMember($user))
				return true;

			return false;
		});

		foreach ($groups as $group)
		{
			if ($group->hasPermission($permissionName))
				return $group->getName();
		}

		return false;
	}
}