<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\DataStorage\DataStorageFactory;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class PermissionCommands extends BaseModule
{
	use ContainerTrait;

	/**
	 * PermissionCommands constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the available groups. No arguments.');
		CommandHandler::fromContainer($container)
			->registerCommand('lsgroups', [$this, 'lsgroupsCommand'], $commandHelp, 0, 0);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows if validation passes for a certain permission. Usage: validate [permission] ([username])');
		CommandHandler::fromContainer($container)
			->registerCommand('validate', [$this, 'validateCommand'], $commandHelp, 1, 2);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Creates a permission group. Usage: creategroup [group name]');
		CommandHandler::fromContainer($container)
			->registerCommand('creategroup', [$this, 'creategroupCommand'], $commandHelp, 1, 1, 'creategroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Deletes a permission group. Usage: delgroup [group name] yes');
		CommandHandler::fromContainer($container)
			->registerCommand('delgroup', [$this, 'delgroupCommand'], $commandHelp, 1, 2, 'delgroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Add a member to a group in the permissions system. Usage: addmember [group name] [nickname]');
		CommandHandler::fromContainer($container)
			->registerCommand('addmember', [$this, 'addmemberCommand'], $commandHelp, 2, 2, 'addmembertogroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Remove a member from a group in the permissions system. Usage: delmember [group name] [nickname]');
		CommandHandler::fromContainer($container)
			->registerCommand('delmember', [$this, 'delmemberCommand'], $commandHelp, 2, 2, 'delmemberfromgroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Add a permission to a permission group. Usage: allow [group name] [permission]');
		CommandHandler::fromContainer($container)
			->registerCommand('allow', [$this, 'allowCommand'], $commandHelp, 2, 2, 'allow');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Remove a permission from a permission group. Usage: deny [group name] [permission]');
		CommandHandler::fromContainer($container)
			->registerCommand('deny', [$this, 'denyCommand'], $commandHelp, 2, 2, 'deny');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('List all members in a permission group. Usage: lsmembers [group name]');
		CommandHandler::fromContainer($container)
			->registerCommand('lsmembers', [$this, 'lsmembersCommand'], $commandHelp, 1, 1, 'listgroupmembers');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('List permissions given to the specified group. Usage: lsperms [group name]');
		CommandHandler::fromContainer($container)
			->registerCommand('lsperms', [$this, 'lspermsCommand'], $commandHelp, 1, 1, 'listgrouppermissions');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Links a channel to a permission group, so a group only takes effect in said channel. Usage: linkgroup [group name] ([channel name])');
		CommandHandler::fromContainer($container)
			->registerCommand('linkgroup', [$this, 'linkgroupCommand'], $commandHelp, 1, 2, 'linkgroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Unlinks a channel from a permission group, so the group no longer takes effect in said channel. Usage: unlinkgroup [group name] ([channel name])');
		CommandHandler::fromContainer($container)
			->registerCommand('unlinkgroup', [$this, 'unlinkgroupCommand'], $commandHelp, 1, 2, 'unlinkgroup');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows info about a group. Usage: groupinfo [group name]');
		CommandHandler::fromContainer($container)
			->registerCommand('groupinfo', [$this, 'groupinfoCommand'], $commandHelp, 1, 2, 'groupinfo');


		$this->setContainer($container);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function allowCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];
		$permission = $args[1];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group is already allowed to do that.' => $group ? $group->getAllowedPermissions()->contains($permission) : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getAllowedPermissions()
			->append($permission);
		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': This group is now allowed the permission "' . $permission . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function denyCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];
		$permission = $args[1];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group is not allowed to do that.' => $group ? !$group->getAllowedPermissions()->contains($permission) : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getAllowedPermissions()
			->removeAll($permission);
		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': This group is now denied the permission "' . $permission . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function lspermsCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];

		$checks = [
			'This group does not exist.' => !PermissionGroupCollection::fromContainer($container)
				->offsetExists($groupName)
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$perms = $group->getAllowedPermissions()
			->values();
		Queue::fromContainer($container)
			->privmsg($source->getName(),
				$user->getNickname() . ': The following permissions are set for this group: ' . implode(', ', $perms));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function lsmembersCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not contain members.' => $group ? $group->isModeGroup() : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$members = $group->getUserCollection()
			->values();
		Queue::fromContainer($container)
			->privmsg($source->getName(), sprintf('%s: The following members are in this group: %s',
				$user->getNickname(),
				implode(', ', $members)));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function addmemberCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];
		$nickname = $args[1];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		/** @var User $userToAdd */
		$userToAdd = $source->getUserCollection()->findByNickname($nickname);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not contain members.' => $group ? $group->isModeGroup() : false,
			'This user is not in my current database, in this channel, or is not logged in to services.' => empty($userToAdd) || empty($userToAdd->getIrcAccount()) || in_array($userToAdd->getIrcAccount(), ['*', '0'])
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getUserCollection()
			->append($userToAdd->getIrcAccount());

		Queue::fromContainer($container)
			->privmsg($source->getName(),
				sprintf('%s: User %s (identified by %s) has been added to the permission group "%s"',
					$user->getNickname(),
					$nickname,
					$userToAdd->getIrcAccount(),
					$groupName));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function delmemberCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];
		$nickname = $args[1];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		/** @var User $userToAdd */
		$userToAdd = $source->getUserCollection()->findByNickname($nickname);

		$checks = [
			'This group does not exist.' => empty($group),
			'This user is not in the group, in this channel, or not online.' => empty($userToAdd) || ($group && !$group->getUserCollection()
						->contains($userToAdd->getIrcAccount()))
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getUserCollection()
			->removeAll($userToAdd->getIrcAccount());

		Queue::fromContainer($container)
			->privmsg($source->getName(),
				sprintf('%s: User %s (identified by %s) has been removed from the permission group "%s"',
					$user->getNickname(),
					$nickname,
					$userToAdd->getIrcAccount(),
					$groupName));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function validateCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		if (empty($args[1]))
			$valUser = $user;
		elseif (($valUser = $source->getUserCollection()
				->findByNickname($args[1])) == false
		)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'This user does not exist or is not online.');

			return;
		}

		$perm = $args[0];

		$result = Validator::fromContainer($container)
			->isAllowedTo($perm, $valUser, $source);

		if ($result)
		{
			$message = sprintf('%s passes validation for permission "%s" in this context. (permitted by group: %s)',
				$valUser->getNickname(),
				$perm,
				$result);
		}
		else
		{
			$message = $valUser->getNickname() . ' does not pass validation for permission "' . $perm . '" in this context.';
		}

		Queue::fromContainer($container)
			->privmsg($source->getName(), $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function lsgroupsCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		/** @var string[] $groups */
		$groups = PermissionGroupCollection::fromContainer($this->getContainer())
			->keys();

		Queue::fromContainer($container)
			->privmsg($source->getName(), 'Available groups: ' . implode(', ', $groups));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function creategroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];

		$checks = [
			'A group with this name already exists.' => PermissionGroupCollection::fromContainer($container)->offsetExists($groupName)
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$groupObj = new PermissionGroup();
		PermissionGroupCollection::fromContainer($this->getContainer())
			->offsetSet($groupName, $groupObj);

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': The group "' . $groupName . '" was successfully created.');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function delgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not be removed.' => $group ? $group->isModeGroup() : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$storage = DataStorageFactory::getStorage('permissiongroups');
		$storage->delete($groupName);

		PermissionGroupCollection::fromContainer($container)
			->offsetUnset($groupName);

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': The group "' . $groupName . '" was successfully deleted.');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function linkgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];
		$channel = $args[1] ?? $source->getName();

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not be linked.' => $group ? $group->isModeGroup() : false,
			'The group is already linked to this channel.' => $group ? $group->getChannelCollection()->contains($channel) : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getChannelCollection()
			->append($channel);

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': This group is now linked with channel "' . $channel . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function unlinkgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];
		$channel = $args[1] ?? $source->getName();

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not be linked.' => $group ? $group->isModeGroup() : false,
			'The group is not linked to this channel.' => $group ? !$group->getChannelCollection()->contains($channel) : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getChannelCollection()
			->removeAll($channel);

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': This group is now no longer linked with channel "' . $channel . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function groupinfoCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$groupName = $args[0];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		if (empty($group))
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		$channels = implode(', ', $group->getChannelCollection()
			->values());
		$members = implode(', ', $group->getUserCollection()
			->values());
		$permissions = implode(', ', $group->getAllowedPermissions()
			->values());

		$lines = [
			'This group is linked to the following channels:',
			$channels,
			'This group contains the following members:',
			$members,
			'This group is allowed the following permissions:',
			$permissions
		];

		// Avoid sending empty lines.
		$lines = array_filter($lines);

		foreach ($lines as $line)
			Queue::fromContainer($container)
				->privmsg($source->getName(), $line);
	}

	/**
	 * @param array $checks
	 * @param Channel $source
	 * @param User $user
	 *
	 * @return bool
	 */
	protected function doChecks(array $checks, Channel $source, User $user)
	{
		foreach ($checks as $string => $check)
		{
			if (!$check)
				continue;

			Queue::fromContainer($this->getContainer())
				->privmsg($source->getName(), $user->getNickname() . ': ' . $string);

			return false;
		}

		return true;
	}
}