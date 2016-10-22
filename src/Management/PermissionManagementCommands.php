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

namespace WildPHP\Core\Management;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandRegistrar;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Permissions\CriteriaCollection;
use WildPHP\Core\Permissions\GlobalPermissionDictionary;
use WildPHP\Core\Permissions\Permission;
use WildPHP\Core\Permissions\PermissionCriteria;
use WildPHP\Core\Users\User;

class PermissionManagementCommands
{
	public function __construct()
	{
		GlobalPermissionDictionary::addPermission(new Permission('canManagePermissions'));
		CommandRegistrar::registerCommand('addcriteria', [$this, 'addCriteriaCommand']);
		CommandRegistrar::registerCommand('delcriteria', [$this, 'delCriteriaCommand']);
	}

	public static function addCriteriaCommand(Channel $source, User $user, array $args, Queue $queue)
	{
		$permission = GlobalPermissionDictionary::getPermission('canManagePermissions');
		$canDo = $permission->allows($user->getIrcAccount(), $source->getName(), $source->getModesForUser($user));

		if (!$canDo)
		{
			$queue->privmsg($source->getName(), 'You do not have permission to do this.');

			return;
		}

		if (count($args) != 4)
		{
			$queue->privmsg($source->getName(), 'Not enough parameters. Usage: addcriteria [permission] [account name] [channel] [mode]');

			return;
		}

		$permission = $args[0];
		$permission = GlobalPermissionDictionary::getPermission($permission);

		if ($permission == false)
		{
			$queue->privmsg($source->getName(), 'A permission with that name does not exist or is not registered.');

			return;
		}

		$accountName = $args[1];
		$channel = $args[2];
		$mode = $args[3];

		if ($accountName == '*')
			$accountName = '';
		if ($channel == '*')
			$channel = '';
		if ($mode == '*')
			$mode = '';

		$permission->getCriteriaCollection()->add(new PermissionCriteria($accountName, $channel, $mode));
		$queue->privmsg($source->getName(), 'Consider that registered!');
	}

	public static function delCriteriaCommand(Channel $source, User $user, array $args, Queue $queue)
	{
		$permission = GlobalPermissionDictionary::getPermission('canManagePermissions');
		$canDo = $permission->allows($user->getIrcAccount(), $source->getName(), $source->getModesForUser($user));

		if (!$canDo)
		{
			$queue->privmsg($source->getName(), 'You do not have permission to do this.');

			return;
		}

		if (count($args) != 4)
		{
			$queue->privmsg($source->getName(), 'Not enough parameters. Usage: delcriteria [permission] [account name] [channel] [mode]');

			return;
		}

		$permission = $args[0];
		$permission = GlobalPermissionDictionary::getPermission($permission);

		if ($permission == false)
		{
			$queue->privmsg($source->getName(), 'A permission with that name does not exist or is not registered.');

			return;
		}

		$accountName = $args[1];
		$channel = $args[2];
		$mode = $args[3];

		if ($accountName == '*')
			$accountName = '';
		if ($channel == '*')
			$channel = '';
		if ($mode == '*')
			$mode = '';

		$numRemoved = $permission->getCriteriaCollection()->removeEvery($accountName, $channel, $mode);

		$queue->privmsg($source->getName(), 'Successfully removed ' . $numRemoved . ' matching criteria.');
	}
}