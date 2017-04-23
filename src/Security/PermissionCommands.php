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
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\Commands\CommandRegistrar;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Users\GlobalUserCollection;
use WildPHP\Core\Users\User;

class PermissionCommands
{
	public function __construct()
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the available groups. No arguments.');
		CommandRegistrar::registerCommand('lsgroups', array($this, 'lsgroupsCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows if validation passes for a certain permission.');
		$commandHelp->addPage('Usage: hasperm [permission] ([username])');
		CommandRegistrar::registerCommand('hasperm', array($this, 'haspermCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Adds a permission group to the permissions system.');
		$commandHelp->addPage('Usage: addgroup [group name]');
		CommandRegistrar::registerCommand('addgroup', array($this, 'addgroupCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Removes a permission group from the permissions system.');
		$commandHelp->addPage('Usage: removegroup [group name] yes');
		CommandRegistrar::registerCommand('removegroup', array($this, 'removegroupCommand'), $commandHelp);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function haspermCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$perm = $args[0];

		if (empty($args[1]) || ($valUser = GlobalUserCollection::getUserByNickname($args[1])) == false)
			$valUser = $user;

		$result = Validator::isAllowedTo($perm, $valUser, $source);

		if ($result)
			$queue->privmsg($source->getName(), $valUser->getNickname() . ' passes validation for permission "' . $perm . '" in this context. (reason: ' . $result . ')');
		else
			$queue->privmsg($source->getName(), $valUser->getNickname() . ' does not pass validation for permission "' . $perm . '" in this context.');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function lsgroupsCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$groups = GlobalPermissionGroupCollection::getPermissionGroupCollection()->toArray();

		$groupList = [];
		foreach ($groups as $group)
			$groupList[] = $group->getName();
		$queue->privmsg($source->getName(), 'Available groups: ' . implode(', ', $groupList));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function addgroupCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('addgroup', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to add a group.');

		$groupName = $args[0];
		$groups = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			if ($item->getName() == $groupName)
				return true;

			return false;
		});

		if (!empty($groups))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': A group with this name already exists.');

		$groupObj = new PermissionGroup($groupName);
		GlobalPermissionGroupCollection::getPermissionGroupCollection()->add($groupObj);
		$queue->privmsg($source->getName(), $user->getNickname() . ': The group "' . $groupName . '" was successfully created.');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function removegroupCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('removegroup', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to remove a group.');

		if ($args[1] != 'yes')
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Please make sure that you want to delete groups and try again.');

		$groupName = $args[0];
		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->remove(function ($item) use ($groupName)
		{
			if ($item->getName() == $groupName)
				return true;

			return false;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': A group with this name does not exist.');

		$queue->privmsg($source->getName(), $user->getNickname() . ': The group "' . $groupName . '" was successfully deleted.');
	}
}