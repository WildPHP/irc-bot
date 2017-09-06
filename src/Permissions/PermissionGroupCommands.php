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
use WildPHP\Core\Commands\JoinedChannelParameter;
use WildPHP\Core\Commands\ParameterStrategy;
use WildPHP\Core\Commands\StringParameter;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class PermissionGroupCommands extends BaseModule
{
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
				new ParameterStrategy(0, 0),
				new CommandHelp([
					'Shows the available groups. No arguments.'
				]),
				'lsgroups'
			),
			['lsg']);

		CommandHandler::fromContainer($container)->registerCommand('validate',
			new Command(
				[$this, 'validateCommand'],
				new ParameterStrategy(1, 2, [
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
				new ParameterStrategy(1, 1, [
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
				new ParameterStrategy(1, 1, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection)
				]),
				new CommandHelp([
					'Deletes a permission group. Usage: delgroup [group name]'
				]),
				'delgroup'
			),
			['rmgroup', 'rmg', 'removegroup', '-group', '-g']);

		CommandHandler::fromContainer($container)->registerCommand('linkgroup',
			new Command(
				[$this, 'linkgroupCommand'],
				new ParameterStrategy(1, 2, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'channel' => new JoinedChannelParameter(ChannelCollection::fromContainer($container))
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
				new ParameterStrategy(1, 2, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'channel' => new JoinedChannelParameter(ChannelCollection::fromContainer($container))
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
				new ParameterStrategy(1, 1, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection)
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
		/** @var PermissionGroup $group */
		$group = $args['group'];

		$checks = [
			'This group may not be removed.' => $group ? $group->isModeGroup() : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		PermissionGroupCollection::fromContainer($container)
			->removeAll($group);

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': The given group was successfully deleted.');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function linkgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		/** @var PermissionGroup $group */
		$group = $args['group'];
		
		/** @var Channel $channel */
		$channel = $args['channel'] ?? $source;

		$checks = [
			'This group may not be linked.' => $group ? $group->isModeGroup() : false,
			'The group is already linked to this channel.' => $group ? $group->getChannelCollection()->contains($channel) : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getChannelCollection()
			->append($channel->getName());

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': This group is now linked with channel "' . $channel->getName() . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function unlinkgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		/** @var PermissionGroup $group */
		$group = $args['group'];
		
		/** @var Channel $channel */
		$channel = $args['channel'] ?? $source;

		$checks = [
			'This group may not be linked.' => $group ? $group->isModeGroup() : false,
			'The group is not linked to this channel.' => $group ? !$group->getChannelCollection()->contains($channel->getName()) : false
		];

		if (!$this->doChecks($checks, $source, $user))
			return;

		$group->getChannelCollection()
			->removeAll($channel->getName());

		Queue::fromContainer($container)
			->privmsg($source->getName(), $user->getNickname() . ': This group is now no longer linked with channel "' . $channel->getName() . '"');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function groupinfoCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		/** @var PermissionGroup $group */
		$group = $args['group'];

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
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}