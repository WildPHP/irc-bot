<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Commands\Command;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\Commands\JoinedChannelNameParameter;
use WildPHP\Core\Commands\ParameterDefinitions;
use WildPHP\Core\Commands\StringParameter;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class PermissionGroupCommands extends BaseModule
{
	use ContainerTrait;

	/**
	 * PermissionCommands constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$permissionGroupCollection = PermissionGroupCollection::fromContainer($container);
		
		CommandHandler::fromContainer($container)->registerCommand('lsgroups',
			new Command(
				[$this, 'lsgroupsCommand'],
				new ParameterDefinitions(0, 0),
				new CommandHelp([
					'Shows the available groups. No arguments.'
				]),
				'lsgroups'
			),
			['lsg']);

		CommandHandler::fromContainer($container)->registerCommand('validate',
			new Command(
				[$this, 'validateCommand'],
				new ParameterDefinitions(1, 2, [
					'permission' => new StringParameter(),
					'username' => new StringParameter()
				]),
				new CommandHelp([
					'Shows if validation passes for a certain permission. Usage: validate [permission] ([username])'
				])
			),
			['val', 'v']);

		CommandHandler::fromContainer($container)->registerCommand('creategroup',
			new Command(
				[$this, 'creategroupCommand'],
				new ParameterDefinitions(1, 1, [
					'groupName' => new StringParameter()
				]),
				new CommandHelp([
					'Creates a permission group. Usage: creategroup [group name]'
				]),
				'creategroup'
			),
			['newgroup', '+group', '+g', 'addgroup']);

		CommandHandler::fromContainer($container)->registerCommand('delgroup',
			new Command(
				[$this, 'delgroupCommand'],
				new ParameterDefinitions(1, 1, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection)
				]),
				new CommandHelp([
					'Deletes a permission group. Usage: delgroup [group name]'
				]),
				'delgroup'
			),
			['rmgroup', 'rmg', 'removegroup', '-group', '-g']);

		CommandHandler::fromContainer($container)->registerCommand('addmember',
			new Command(
				[$this, 'addmemberCommand'],
				new ParameterDefinitions(2, 2, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'nickname' => new StringParameter()
				]),
				new CommandHelp([
					'Add a member to a group in the permissions system. Usage: addmember [group name] [nickname]'
				]),
				'addmember'
			),
			['+member', '+m']);

		CommandHandler::fromContainer($container)->registerCommand('delmember',
			new Command(
				[$this, 'delmemberCommand'],
				new ParameterDefinitions(2, 2, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'nickname' => new StringParameter()
				]),
				new CommandHelp([
					'Remove a member from a group in the permissions system. Usage: delmember [group name] [nickname]'
				]),
				'delmember'
			),
			['-member', '-m']);

		CommandHandler::fromContainer($container)->registerCommand('allow',
			new Command(
				[$this, 'allowCommand'],
				new ParameterDefinitions(2, 2, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'permission' => new StringParameter()
				]),
				new CommandHelp([
					'Add a permission to a permission group. Usage: allow [group name] [permission]'
				]),
				'allow'
			));

		CommandHandler::fromContainer($container)->registerCommand('deny',
			new Command(
				[$this, 'denyCommand'],
				new ParameterDefinitions(2, 2, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'permission' => new StringParameter()
				]),
				new CommandHelp([
					'Remove a permission from a permission group. Usage: deny [group name] [permission]'
				]),
				'deny'
			));

		CommandHandler::fromContainer($container)->registerCommand('lsmembers',
			new Command(
				[$this, 'lsmembersCommand'],
				new ParameterDefinitions(1, 1, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection)
				]),
				new CommandHelp([
					'List all members in a permission group. Usage: lsmembers [group name]'
				]),
				'lsmembers'
			),
			['lsm']);

		CommandHandler::fromContainer($container)->registerCommand('lsperms',
			new Command(
				[$this, 'lspermsCommand'],
				new ParameterDefinitions(1, 1, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection)
				]),
				new CommandHelp([
					'List all members in a permission group. Usage: lsperms [group name]'
				]),
				'lsperms'
			),
			['lsp']);

		CommandHandler::fromContainer($container)->registerCommand('linkgroup',
			new Command(
				[$this, 'linkgroupCommand'],
				new ParameterDefinitions(1, 2, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'channel' => new JoinedChannelNameParameter(ChannelCollection::fromContainer($container))
				]),
				new CommandHelp([
					'Links a channel to a permission group, so a group only takes effect in said channel. Usage: linkgroup [group name] ([channel name])',
					'The channel to be linked, if specified, must be joined by the bot for this command to work.'
				]),
				'linkgroup'
			),
			['lg']);

		CommandHandler::fromContainer($container)->registerCommand('unlinkgroup',
			new Command(
				[$this, 'unlinkgroupCommand'],
				new ParameterDefinitions(1, 2, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'channel' => new JoinedChannelNameParameter(ChannelCollection::fromContainer($container))
				]),
				new CommandHelp([
					'Unlinks a channel from a permission group, so the group no longer takes effect in said channel. Usage: unlinkgroup [group name] ([channel name])',
					'The channel to be linked, if specified, must be joined by the bot for this command to work.'
				]),
				'unlinkgroup'
			),
			['ulg']);

		CommandHandler::fromContainer($container)->registerCommand('groupinfo',
			new Command(
				[$this, 'groupinfoCommand'],
				new ParameterDefinitions(1, 1, [
					'groupName' => new ExistingPermissionGroupParameter($permissionGroupCollection)
				]),
				new CommandHelp([
					'Shows info about a group. Usage: groupinfo [group name]'
				]),
				'groupinfo'
			),
			['gi']);

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
		list ($groupName, $permission) = $args;

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
		list ($groupName, $permission) = $args;

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
		$groupName = $args['groupName'];

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
		$groupName = $args['groupName'];

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
		list ($groupName, $nickname) = $args;

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		/** @var User $userToAdd */
		$userToAdd = $source->getUserCollection()->findByNickname($nickname);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not contain members.' => $group ? $group->isModeGroup() : false,
			'This user is not in my current database, in this channel, or is not logged in to services.' =>
				empty($userToAdd) || empty($userToAdd->getIrcAccount()) || in_array($userToAdd->getIrcAccount(), ['*', '0'])
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
		list ($groupName, $nickname) = $args;

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
		if (empty($args['username']))
			$valUser = $user;
		elseif (($valUser = $source->getUserCollection()->findByNickname($args['username'])) == false)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'This user does not exist or is not online.');

			return;
		}

		$perm = $args['permission'];

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
		$groupName = $args['groupName'];

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
		$groupName = $args['groupName'];

		/** @var PermissionGroup|false $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

		$checks = [
			'This group does not exist.' => empty($group),
			'This group may not be removed.' => $group ? $group->isModeGroup() : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

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

		if (!PermissionGroupCollection::fromContainer($container)->offsetExists($groupName))
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': This group does not exist.');

			return;
		}

		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($container)
			->offsetGet($groupName);

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

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}