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
		CommandRegistrar::registerCommand('lsgroups', [$this, 'lsgroupsCommand'], $commandHelp, 0, 0);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows if validation passes for a certain permission.');
		$commandHelp->addPage('Usage: hasperm [permission] ([username])');
		CommandRegistrar::registerCommand('hasperm', [$this, 'haspermCommand'], $commandHelp, 1, 2);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Adds a permission group to the permissions system.');
		$commandHelp->addPage('Usage: addgroup [group name]');
		CommandRegistrar::registerCommand('addgroup', [$this, 'addgroupCommand'], $commandHelp, 1, 1, 'addgroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Removes a permission group from the permissions system.');
		$commandHelp->addPage('Usage: removegroup [group name] yes');
		CommandRegistrar::registerCommand('removegroup', [$this, 'removegroupCommand'], $commandHelp, 1, 2, 'removegroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Add a member to a group in the permissions system.');
		$commandHelp->addPage('Usage: addmember [group name] [nickname]');
		CommandRegistrar::registerCommand('addmember', [$this, 'addmemberCommand'], $commandHelp, 2, 2, 'addmembertogroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Remove a member from a group in the permissions system.');
		$commandHelp->addPage('Usage: removemember [group name] [nickname]');
		CommandRegistrar::registerCommand('removemember', [$this, 'removememberCommand'], $commandHelp, 2, 2, 'removememberfromgroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Add a permission to a permission group.');
		$commandHelp->addPage('Usage: allow [group name] [permission]');
		CommandRegistrar::registerCommand('allow', [$this, 'allowCommand'], $commandHelp, 2, 2, 'allow');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Remove a permission from a permission group.');
		$commandHelp->addPage('Usage: deny [group name] [permission]');
		CommandRegistrar::registerCommand('deny', [$this, 'denyCommand'], $commandHelp, 2, 2, 'deny');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('List all members in a permission group.');
		$commandHelp->addPage('Usage: lsmembers [group name]');
		CommandRegistrar::registerCommand('lsmembers', [$this, 'lsmembersCommand'], $commandHelp, 1, 1, 'listgroupmembers');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('List all permissions allowed to this group.');
		$commandHelp->addPage('Usage: lsperms [group name]');
		CommandRegistrar::registerCommand('lsperms', [$this, 'lspermsCommand'], $commandHelp, 1, 1, 'listgrouppermissions');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function allowCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$groupName = $args[0];
		$permission = $args[1];

		$group = $this->findGroupByName($groupName);

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		if ($group->hasPermission($permission))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': The group is already allowed to do that.');

			return;
		}

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
		$groupName = $args[0];
		$permission = $args[1];

		$group = $this->findGroupByName($groupName);

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		if (!$group->hasPermission($permission))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': The group is not allowed to do that.');

			return;
		}

		$group->removePermission($permission);
		$queue->privmsg($source->getName(), $user->getNickname() . ': This group is now denied the permission "' . $permission . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function lspermsCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$groupName = $args[0];

		$group = $this->findGroupByName($groupName);

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		$perms = $group->listPermissions();
		$queue->privmsg($source->getName(), $user->getNickname() . ': The following permissions are set for this group: ' . implode(', ', $perms));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function lsmembersCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$groupName = $args[0];

		$group = $this->findGroupByName($groupName);

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		if (!$group->getCanHaveMembers())
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group cannot contain members.');

			return;
		}

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
		$groupName = $args[0];
		$nickname = $args[1];

		$group = $this->findGroupByName($groupName);

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		$userToAdd = GlobalUserCollection::getUserByNickname($nickname);

		if (empty($userToAdd) || empty($userToAdd))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This user is not in my current database or is not logged in to services.');

			return;
		}

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
		$groupName = $args[0];
		$nickname = $args[1];

		$group = $this->findGroupByName($groupName);

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		$userToAdd = GlobalUserCollection::getUserByNickname($nickname);

		if (empty($userToAdd) && !$group->isMemberByIrcAccount($nickname))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This user is not in the group.');

			return;
		}

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
		if (empty($args[1]) || ($valUser = GlobalUserCollection::getUserByNickname($args[1])) == false)
		{
			$valUser = $user;
		}

		$perm = $args[0];

		$result = Validator::isAllowedTo($perm, $valUser, $source);

		if ($result)
		{
			$message = $valUser->getNickname() . ' passes validation for permission "' . $perm . '" in this context. (permitted by group: ' . $result . ')';
		}

		else
		{
			$message = $valUser->getNickname() . ' does not pass validation for permission "' . $perm . '" in this context.';
		}

		$queue->privmsg($source->getName(), $message);
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
		{
			$groupList[] = $group->getName();
		}
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
		$groupName = $args[0];
		$groups = $this->findGroupByName($groupName);

		if (!empty($groups))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': A group with this name already exists.');

			return;
		}

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
		$groupName = $args[0];

		if ($groupName == 'op' || $groupName == 'voice')
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This group may not be removed.');

			return;
		}

		$group = GlobalPermissionGroupCollection::getPermissionGroupCollection()->remove(function (PermissionGroup $item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});

		if (empty($group))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': A group with this name does not exist.');

			return;
		}

		$queue->privmsg($source->getName(), $user->getNickname() . ': The group "' . $groupName . '" was successfully deleted.');
	}

	/**
	 * @param string $groupName
	 * @return bool|PermissionGroup
	 */
	protected function findGroupByName(string $groupName)
	{
		return GlobalPermissionGroupCollection::getPermissionGroupCollection()->find(function (PermissionGroup $item) use ($groupName)
		{
			return $item->getName() == $groupName;
		});
	}
}