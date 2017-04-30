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
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Users\User;

class HelpCommand
{
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the help pages for a specific command.');
		$commandHelp->addPage('Usage: help [command] [page]');
		$container->getCommandHandler()->registerCommand('help', [$this, 'helpCommand'], $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the list of available commands. No arguments.');
		$container->getCommandHandler()->registerCommand('lscommands', [$this, 'lscommandsCommand'], $commandHelp);
		$container->getCommandHandler()->registerCommand('quit', [$this, 'testQuit']);

		$container->getCommandHandler()->registerCommand('lsnicks', function (Channel $source, User $user, $args, ComponentContainer $container)
		{
			$nicks = $source->getUserCollection()->getAllNicknames();
			$container->getQueue()->privmsg($user->getNickname(), $source->getName() . ': ' . implode(', ', $nicks));
		});

		$container->getCommandHandler()->registerCommand('aboutme', function (Channel $source, User $user, $args, ComponentContainer $container)
		{
			if ($source->getName() != $user->getNickname());
				$container->getQueue()->privmsg($source->getName(), $user->getNickname() . ': I am sending you what I know in private.');

			$container->getQueue()->privmsg($user->getNickname(), 'You are ' . $user->getNickname() . '. Your hostname is ' . $user->getHostname() . '.');
			if (empty($user->getIrcAccount()))
				$container->getQueue()->privmsg($user->getNickname(), 'I cannot identify you per your network services account, therefore my functionality may be limited.');
			else
				$container->getQueue()->privmsg($user->getNickname(), 'I have identified you to be ' . $user->getIrcAccount() . ' based on your network services account, and will use this account for identification purposes in the future.');

			$channelNames = [];
			foreach ($user->getChannelCollection()->toArray() as $channel)
				$channelNames[] = $channel->getName();
			$channelCount = count($channelNames);
			$container->getQueue()->privmsg($user->getNickname(), 'I can see you in ' . $channelCount . ' channels, namely:');
			$container->getQueue()->privmsg($user->getNickname(), implode(', ', $channelNames));

			if (!empty($user->getIrcAccount()))
			{
				$userGroups = $container->getPermissionGroupCollection()->findAllGroupsForIrcAccount($user->getIrcAccount());

				$groups = [];
				foreach ($userGroups->toArray() as $userGroup)
				{
					$groups[] = $userGroup->getName();
				}

				$container->getQueue()->privmsg($user->getNickname(), 'You are in the following permission groups: ' . implode(', ', $groups));

				if ($container->getConfiguration()->get('owner')->getValue() == $user->getIrcAccount())
					$container->getQueue()->privmsg($user->getNickname(), 'Additionally, you are my owner! I thank my existence to you.');
			}

			$container->getQueue()->privmsg($user->getNickname(), 'That is about it, then!');
		});
	}

	public function testQuit(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$result = $container->getValidator()->isAllowedTo('quit', $user, $source);
		if (!$result)
		{
			return;
		}
		$container->getQueue()->quit('Quit command given by ' . $user->getNickname());
	}

	public function lscommandsCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$commands = array_keys($container->getCommandHandler()->getCommandDictionary()->toArray());
		$commands = implode(', ', $commands);
		$container->getQueue()->privmsg($source->getName(), 'Available commands: ' . $commands);
	}

	public function helpCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		if (empty($args))
		{
			$args[0] = 'help';
			$args[1] = '1';
		}

		$command = $args[0];
		$page = !empty($args[1]) ? $args[1] : 1; // Take into account arrays starting at position 0.

		if (!$container->getCommandHandler()->getCommandDictionary()->keyExists($command))
		{
			$container->getQueue()->privmsg($source->getName(), 'That command does not exist, sorry!');

			return;
		}

		$commandObject = $container->getCommandHandler()->getCommandDictionary()[$command];
		$helpObject = $commandObject->getHelp();
		if ($helpObject == null || !($helpObject instanceof CommandHelp))
		{
			$container->getQueue()->privmsg($source->getName(), 'There is no help available for this command.');

			return;
		}

		$pageToGet = $page - 1;
		if (!$helpObject->indexExists($pageToGet))
		{
			$container->getQueue()->privmsg($source->getName(), 'That page does not exist for this command.');
			$container->getLogger()->debug('Tried to grab invalid page from CommandHelp object.', [
				'page' => $pageToGet,
				'object' => $helpObject
			]);

			return;
		}

		$contents = $helpObject->getPageAt($pageToGet);
		$pageCount = $helpObject->getPageCount();
		$container->getQueue()->privmsg($source->getName(), $command . ': ' . $contents . ' (page ' . $page . ' of ' . $pageCount . ')');
	}
}