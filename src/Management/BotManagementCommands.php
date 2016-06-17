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
use WildPHP\Core\Permissions\GlobalPermissionDictionary;
use WildPHP\Core\Permissions\Permission;
use WildPHP\Core\Users\User;

class BotManagementCommands
{
	public function __construct()
	{
		GlobalPermissionDictionary::addPermission(new Permission('canQuit'));
		CommandRegistrar::registerCommand('quit', [$this, 'quitCommand']);
	}
	
	public static function quitCommand(Channel $source, User $user, array $args, Queue $queue)
	{
		$permission = GlobalPermissionDictionary::getPermission('canQuit');
		if (!$permission->allows($user->getIrcAccount(), $source->getName(), $source->getModesForUser($user)))
		{
			$queue->privmsg($source->getName(), 'You do not have permission to do this.');
			return;
		}

		$message = !empty($args) ? implode(' ', $args) : 'WildPHP shutting down...';

		$queue->quit($message);
	}
}