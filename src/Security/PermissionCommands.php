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

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Add a member to a group in the permissions system.');
		$commandHelp->addPage('Usage: addmember [group name] [nickname]');
		CommandRegistrar::registerCommand('addmember', array($this, 'addmemberCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Remove a member from a group in the permissions system.');
		$commandHelp->addPage('Usage: removemember [group name] [nickname]');
		CommandRegistrar::registerCommand('removemember', array($this, 'removememberCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Add a permission to a permission group.');
		$commandHelp->addPage('Usage: allow [group name] [permission]');
		CommandRegistrar::registerCommand('allow', array($this, 'allowCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Remove a permission from a permission group.');
		$commandHelp->addPage('Usage: deny [group name] [permission]');
		CommandRegistrar::registerCommand('deny', array($this, 'denyCommand'), $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('List all members in a permission group.');
		$commandHelp->addPage('Usage: lsmembers [group name]');
		CommandRegistrar::registerCommand('lsmembers', array($this, 'lsmembersCommand'), $commandHelp);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function allowCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('allowpermission', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to change group permissions.');

		if (count($args) != 2)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$groupName = $args[0];
		$permission = $args[1];

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

		if ($group->hasPermission($permission))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': The group is already allowed to do that.');

		$group->addPermission($permission);
		$queue->privmsg($source->getName(), $user->getNickname() . ': This group is now allowed the permission "' . $permission . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function denyCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('denypermission', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to change group permissions.');

		if (count($args) != 2)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$groupName = $args[0];
		$permission = $args[1];

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

		if (!$group->hasPermission($permission))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': The group is not allowed to do that.');

		$group->removePermission($permission);
		$queue->privmsg($source->getName(), $user->getNickname() . ': This group is now denied the permission "' . $permission . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function lsmembersCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('listgroupmembers', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to list group memberships.');

		if (count($args) != 1)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$groupName = $args[0];

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

		$members = $group->getUserCollection();
		$queue->privmsg($source->getName(), $user->getNickname() . ': The following members are in this group: ' . implode(', ', $members));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function addmemberCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('addmembertogroup', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to add a member to a group.');

		if (count($args) != 2)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$groupName = $args[0];
		$nickname = $args[1];

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

		$userToAdd = GlobalUserCollection::getUserByNickname($nickname);

		if (empty($userToAdd))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This user is not in my current database.');

		$group->addMember($userToAdd);
		$queue->privmsg($source->getName(), $user->getNickname() . ': User ' . $nickname . ' (identified by ' . $user->getIrcAccount() . ') has been added to the permission group "' . $groupName . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function removememberCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('removememberfromgroup', $user, $source);

		if (!$result)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to remove a member from a group.');

		if (count($args) != 2)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$groupName = $args[0];
		$nickname = $args[1];

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

		$userToAdd = GlobalUserCollection::getUserByNickname($nickname);

		if (empty($userToAdd) && !$group->isMemberByIrcAccount($nickname))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This user is not in the group.');

		elseif ($group->isMemberByIrcAccount($nickname))
		{
			$group->removeMemberByIrcAccount($nickname);
			$queue->privmsg($source->getName(), $user->getNickname() . ': User ' . $nickname . ' has been removed from the permission group "' . $groupName . '"');

			return;
		}


		$group->removeMember($userToAdd);
		$queue->privmsg($source->getName(), $user->getNickname() . ': User ' . $nickname . ' (identified by ' . $user->getIrcAccount() . ') has been removed from the permission group "' . $groupName . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function haspermCommand(Channel $source, User $user, $args, Queue $queue)
	{
		if (count($args) != 1)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$perm = $args[0];

		if (empty($args[1]) || ($valUser = GlobalUserCollection::getUserByNickname($args[1])) == false)
			$valUser = $user;

		$result = Validator::isAllowedTo($perm, $valUser, $source);

		if ($result)
			$queue->privmsg($source->getName(), $valUser->getNickname() . ' passes validation for permission "' . $perm . '" in this context. (permitted by group: ' . $result . ')');
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

		if (count($args) != 1)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		$groupName = $args[0];
		$groups = GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
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

		if (count($args) < 1)
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Insufficient parameters.');

		if ($args[1] != 'yes')
			return $queue->privmsg($source->getName(), $user->getNickname() . ': Please make sure that you want to delete groups and try again.');

		$groupName = $args[0];

		if ($groupName == 'op' || $groupName == 'voice')
			return $queue->privmsg($source->getName(), $user->getNickname() . ': This group may not be removed.');

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->remove(function ($item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
			return $queue->privmsg($source->getName(), $user->getNickname() . ': A group with this name does not exist.');

		$queue->privmsg($source->getName(), $user->getNickname() . ': The group "' . $groupName . '" was successfully deleted.');
	}
}