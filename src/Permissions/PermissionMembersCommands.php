<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\Command;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\Commands\ParameterStrategy;
use WildPHP\Core\Commands\StringParameter;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class PermissionMembersCommands extends BaseModule
{
	/**
	 * PermissionCommands constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$permissionGroupCollection = PermissionGroupCollection::fromContainer($container);

		CommandHandler::fromContainer($container)->registerCommand('lsmembers',
			new Command(
				[$this, 'lsmembersCommand'],
				new ParameterStrategy(1, 1, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection)
				]),
				new CommandHelp([
					'List all members in a permission group. Usage: lsmembers [group name]'
				]),
				'lsmembers'
			),
			['lsm']);
		
		CommandHandler::fromContainer($container)->registerCommand('addmember',
			new Command(
				[$this, 'addmemberCommand'],
				new ParameterStrategy(2, 2, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
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
				new ParameterStrategy(2, 2, [
					'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
					'nickname' => new StringParameter()
				]),
				new CommandHelp([
					'Remove a member from a group in the permissions system. Usage: delmember [group name] [nickname]'
				]),
				'delmember'
			),
			['-member', '-m']);
		
		$this->setContainer($container);
	}
	
	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function lsmembersCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		/** @var PermissionGroup $group */
		$group = $args['group'];

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
		$nickname = $args['nickname'];

		/** @var PermissionGroup $group */
		$group = $args['group'];

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
				sprintf('%s: User %s (identified by %s) has been added to the specified permission group.',
					$user->getNickname(),
					$nickname,
					$userToAdd->getIrcAccount()));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function delmemberCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$nickname = $args['nickname'];

		/** @var PermissionGroup $group */
		$group = $args['group'];

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
				sprintf('%s: User %s (identified by %s) has been removed from the specified permission group',
					$user->getNickname(),
					$nickname,
					$userToAdd->getIrcAccount()));
	}
	
	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}