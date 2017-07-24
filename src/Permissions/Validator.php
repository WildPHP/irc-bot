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
use WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Users\User;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Validator implements ComponentInterface
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * @var array
	 */
	protected $modes = [];

	/**
	 * Validator constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.005', [$this, 'createModeGroups']);
	}

	/**
	 * @param RPL_ISUPPORT $ircMessage
	 * @param Queue $queue
	 */
	public function createModeGroups(RPL_ISUPPORT $ircMessage, Queue $queue)
	{
		$variables = $ircMessage->getVariables();

		if (!array_key_exists('prefix', $variables) || !preg_match('/\((.+)\)(.+)/', $variables['prefix'], $out))
			return;

		$modes = str_split($out[1]);
		$this->modes = $modes;

		foreach ($modes as $mode)
		{
			if (PermissionGroupCollection::fromContainer($this->getContainer())->offsetExists($mode))
				continue;

			$permGroup = new PermissionGroup();
			$permGroup->setModeGroup(true);
			PermissionGroupCollection::fromContainer($this->getContainer())->offsetSet($mode, $permGroup);
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
		if ($user->getIrcAccount() == Configuration::fromContainer($this->getContainer())['owner'])
			return 'owner';

		if (!empty($channel))
		{
			foreach ($this->modes as $mode)
			{
				if (!$channel->getChannelModes()->isUserInMode($mode, $user))
					continue;

				/** @var PermissionGroup $permGroup */
				$permGroup = PermissionGroupCollection::fromContainer($this->getContainer())
					->offsetGet($mode);

				if ($permGroup->hasPermission($permissionName))
					return $mode;
			}
		}

		$channelName = !empty($channel) ? $channel->getName() : '';

		/** @var Collection $groups */
		$groups = PermissionGroupCollection::fromContainer($this->getContainer())
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
				return $name;
		}

		return false;
	}
}