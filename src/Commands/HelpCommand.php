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

namespace WildPHP\Core\Commands;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Security\GlobalPermissionGroupCollection;
use WildPHP\Core\Security\PermissionGroup;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Users\User;

class HelpCommand
{
	public function __construct()
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the help pages for a specific command.');
		$commandHelp->addPage('Usage: help [command] [page]');
		CommandRegistrar::registerCommand('help', [$this, 'helpCommand'], $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the list of available commands. No arguments.');
		CommandRegistrar::registerCommand('lscommands', [$this, 'lscommandsCommand'], $commandHelp);
		CommandRegistrar::registerCommand('quit', [$this, 'testQuit']);

		CommandRegistrar::registerCommand('lsnicks', function (Channel $source, User $user, $args, Queue $queue)
		{
			$nicks = $source->getUserCollection()->getAllUsersAsString();
			$queue->privmsg($user->getNickname(), $source->getName() . ': ' . implode(', ', $nicks));
		});

		CommandRegistrar::registerCommand('aboutme', function (Channel $source, User $user, $args, Queue $queue)
		{
			if ($source->getName() != $user->getNickname());
				$queue->privmsg($source->getName(), $user->getNickname() . ': I am sending you what I know in private.');

			$queue->privmsg($user->getNickname(), 'You are ' . $user->getNickname() . '. Your hostname is ' . $user->getHostname() . '.');
			if (empty($user->getIrcAccount()))
				$queue->privmsg($user->getNickname(), 'I cannot identify you per your network services account, therefore my functionality may be limited.');
			else
				$queue->privmsg($user->getNickname(), 'I have identified you to be ' . $user->getIrcAccount() . ' based on your network services account, and will use this account for identification purposes in the future.');

			$channelList = array_keys($user->getChannelCollection()->getAllChannels());
			$channelCount = count($channelList);
			$queue->privmsg($user->getNickname(), 'I can see you in ' . $channelCount . ' channels, namely:');
			$queue->privmsg($user->getNickname(), implode(', ', $channelList));

			if (!empty($user->getIrcAccount()))
			{
				$userGroups = GlobalPermissionGroupCollection::getPermissionGroupCollection()->findAllGroupsForIrcAccount($user->getIrcAccount());

				$groups = [];
				foreach ($userGroups as $userGroup)
				{
					$groups[] = $userGroup->getName();
				}

				$queue->privmsg($user->getNickname(), 'You are in the following permission groups: ' . implode(', ', $groups));

				if (Configuration::get('owner')->getValue() == $user->getIrcAccount())
					$queue->privmsg($user->getNickname(), 'Additionally, you are my owner! I thank my existence to you.');
			}

			$queue->privmsg($user->getNickname(), 'That is about it, then!');
		});
	}

	public function testQuit(Channel $source, User $user, $args, Queue $queue)
	{
		$result = Validator::isAllowedTo('quit', $user, $source);
		if (!$result)
		{
			return;
		}
		$queue->quit('Quit command given by ' . $user->getNickname());
	}

	public function lscommandsCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$commands = CommandRegistrar::listCommands();
		$commands = implode(', ', $commands);
		$queue->privmsg($source->getName(), 'Available commands: ' . $commands);
	}

	public function helpCommand(Channel $source, User $user, $args, Queue $queue)
	{
		if (empty($args))
		{
			$args[0] = 'help';
			$args[1] = '1';
		}

		$command = $args[0];
		$page = !empty($args[1]) ? $args[1] : 1; // Take into account arrays starting at position 0.

		if (!GlobalCommandDictionary::getDictionary()->keyExists($command))
		{
			$queue->privmsg($source->getName(), 'That command does not exist, sorry!');

			return;
		}

		$commandObject = GlobalCommandDictionary::getDictionary()[$command];
		$helpObject = $commandObject->getHelp();
		if ($helpObject == null || !($helpObject instanceof CommandHelp))
		{
			$queue->privmsg($source->getName(), 'There is no help available for this command.');

			return;
		}

		$pageToGet = $page - 1;
		if (!$helpObject->indexExists($pageToGet))
		{
			$queue->privmsg($source->getName(), 'That page does not exist for this command.');
			Logger::debug('Tried to grab invalid page from CommandHelp object.', [
				'page' => $pageToGet,
				'object' => $helpObject
			]);

			return;
		}

		$contents = $helpObject->getPageAt($pageToGet);
		$pageCount = $helpObject->getPageCount();
		$queue->privmsg($source->getName(), $command . ': ' . $contents . ' (page ' . $page . ' of ' . $pageCount . ')');
	}
}