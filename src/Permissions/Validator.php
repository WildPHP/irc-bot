<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;

use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Users\User;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Validator implements ComponentInterface
{
	use ComponentTrait;

	/**
	 * @var array
	 */
	protected $modes = [];

	/**
	 * @var string
	 */
	protected $owner = '';

	/**
	 * @var PermissionGroupCollection
	 */
	protected $permissionGroupCollection;

	/**
	 * Validator constructor.
	 *
	 * @param EventEmitter $eventEmitter
	 * @param PermissionGroupCollection $permissionGroupCollection
	 * @param string $owner
	 */
	public function __construct(EventEmitter $eventEmitter, PermissionGroupCollection $permissionGroupCollection, string $owner)
	{
		$eventEmitter->on('irc.line.in.005', [$this, 'createModeGroups']);

		$this->setPermissionGroupCollection($permissionGroupCollection);
		$this->setOwner($owner);
	}

	/**
	 * @param RPL_ISUPPORT $ircMessage
	 */
	public function createModeGroups(RPL_ISUPPORT $ircMessage)
	{
		$variables = $ircMessage->getVariables();

		if (!array_key_exists('prefix', $variables) || !preg_match('/\((.+)\)(.+)/', $variables['prefix'], $out))
			return;

		$modes = str_split($out[1]);
		$this->modes = $modes;

		foreach ($modes as $mode)
		{
			if ($this->permissionGroupCollection->offsetExists($mode))
				continue;

			$permGroup = new PermissionGroup();
			$permGroup->setModeGroup(true);
			$this->permissionGroupCollection->offsetSet($mode, $permGroup);
		}
	}

	/**
	 * @param string $permissionName
	 * @param User $user
	 * @param Channel|null $channel
	 *
	 * @return string|false String with reason on success; boolean false otherwise.
	 */
	public function isAllowedTo(string $permissionName, User $user, ?Channel $channel = null)
	{
		// The order to check in:
		// 0. Is bot owner (has all perms)
		// 1. User OP in channel
		// 2. User Voice in channel
		// 3. User in other group with permission
		if ($user->getIrcAccount() == $this->getOwner())
			return 'owner';

		if (!empty($channel))
		{
			foreach ($this->modes as $mode)
			{
				if (!$channel->getChannelModes()->isUserInMode($mode, $user))
					continue;

				/** @var PermissionGroup $permGroup */
				$permGroup = $this->permissionGroupCollection
					->offsetGet($mode);

				if ($permGroup->hasPermission($permissionName))
					return $mode;
			}
		}

		$channelName = !empty($channel) ? $channel->getName() : '';

		/** @var Collection $groups */
		$groups = $this->permissionGroupCollection
			->filter(function ($item) use ($user)
			{
				/** @var PermissionGroup $item */
				if ($item->isModeGroup())
					return false;

				return $item->getUserCollection()->contains($user->getIrcAccount());
			});

		foreach ((array) $groups as $name => $group)
		{
			/** @var PermissionGroup $group */
			if ($group->hasPermission($permissionName, $channelName))
				return (string) $name;
		}

		return false;
	}

	/**
	 * @return PermissionGroupCollection
	 */
	public function getPermissionGroupCollection(): PermissionGroupCollection
	{
		return $this->permissionGroupCollection;
	}

	/**
	 * @param PermissionGroupCollection $permissionGroupCollection
	 */
	public function setPermissionGroupCollection(PermissionGroupCollection $permissionGroupCollection)
	{
		$this->permissionGroupCollection = $permissionGroupCollection;
	}

	/**
	 * @return array
	 */
	public function getModes(): array
	{
		return $this->modes;
	}

	/**
	 * @return string
	 */
	public function getOwner(): string
	{
		return $this->owner;
	}

	/**
	 * @param string $owner
	 */
	public function setOwner(string $owner)
	{
		$this->owner = $owner;
	}
}