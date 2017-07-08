<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;

use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Users\User;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Validator implements ComponentInterface
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * Validator constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);


	}

	/**
	 * @param Channel $channel
	 * @param User $user
	 *
	 * @return bool
	 */
	public function isUserOPInChannel(Channel $channel, User $user)
	{
		return $channel->getChannelModes()
			->isUserInMode('o', $user);
	}

	/**
	 * @param Channel $channel
	 * @param User $user
	 *
	 * @return bool
	 */
	public function isUserVoicedInChannel(Channel $channel, User $user)
	{
		return $channel->getChannelModes()
			->isUserInMode('v', $user);
	}

	/**
	 * @param string $permissionName
	 * @param User $user
	 * @param Channel|null $channel
	 *
	 * @return string|false String with reason on success; boolean false otherwise.
	 */
	public function isAllowedTo(string $permissionName = '', User $user, Channel $channel = null)
	{
		// The order to check in:
		// 0. Is bot owner (has all perms)
		// 1. User OP in channel
		// 2. User Voice in channel
		// 3. User in other group with permission
		if ($user->getIrcAccount() == Configuration::fromContainer($this->getContainer())['owner'])
			return 'owner';

		if (!empty($channel) && $this->isUserOPInChannel($channel, $user))
		{
			/** @var PermissionGroup $opGroup */
			$opGroup = PermissionGroupCollection::fromContainer($this->getContainer())
				->offsetGet('op');

			if ($opGroup->hasPermission($permissionName))
				return 'op';
		}

		if (!empty($channel) && $this->isUserVoicedInChannel($channel, $user))
		{
			/** @var PermissionGroup $voiceGroup */
			$voiceGroup = PermissionGroupCollection::fromContainer($this->getContainer())
				->offsetGet('voice');

			if ($voiceGroup->hasPermission($permissionName))
				return 'voice';
		}

		$channelName = !empty($channel) ? $channel->getName() : '';

		/** @var Collection $groups */
		$groups = PermissionGroupCollection::fromContainer($this->getContainer())
			->filter(function ($item) use ($user)
			{
				/** @var PermissionGroup $item */
				if (!$item->getCanHaveMembers())
					return false;

				return $item->getUserCollection()->contains($user->getIrcAccount());
			});

		foreach ((array) $groups as $name => $group)
		{
			/** @var PermissionGroup $group */
			if ($group->hasPermission($permissionName, $channelName))
				return $name;
		}

		return false;
	}
}