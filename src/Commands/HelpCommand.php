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
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Security\PermissionGroupCollection;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Users\User;

class HelpCommand
{
	/**
	 * HelpCommand constructor.
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the help pages for a specific command.');
		$commandHelp->addPage('Usage: help [command] [page]');
		CommandHandler::fromContainer($container)
			->registerCommand('help', [$this, 'helpCommand'], $commandHelp);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the list of available commands. No arguments.');
		CommandHandler::fromContainer($container)
			->registerCommand('lscommands', [$this, 'lscommandsCommand'], $commandHelp);
		CommandHandler::fromContainer($container)
			->registerCommand('quit', [$this, 'testQuit']);

		CommandHandler::fromContainer($container)
			->registerCommand('lsnicks',
				function (Channel $source, User $user, $args, ComponentContainer $container)
				{
					$nicks = $source->getUserCollection()
						->getAllNicknames();
					Queue::fromContainer($container)
						->privmsg($user->getNickname(), $source->getName() . ': ' . implode(', ', $nicks));
				});

		CommandHandler::fromContainer($container)
			->registerCommand('eval',
				function (Channel $source, User $user, $args, ComponentContainer $container)
				{
					$code = implode(' ', $args);
					$output = eval($code);
					ob_start();
					var_dump($output);
					$result = ob_get_clean();
					$result = trim(preg_replace('/\s\s+/', ' ', $result));
					Queue::fromContainer($container)
						->privmsg($source->getName(), $result);

				},
				null,
				1,
				-1,
				'eval');

		CommandHandler::fromContainer($container)
			->registerCommand('aboutme',
				function (Channel $source, User $user, $args, ComponentContainer $container)
				{
					if ($source->getName() != $user->getNickname()) ;
					Queue::fromContainer($container)
						->privmsg($source->getName(), $user->getNickname() . ': I am sending you what I know in private.');

					Queue::fromContainer($container)
						->privmsg($user->getNickname(),
							'You are ' . $user->getNickname() . '. Your hostname is ' . $user->getHostname() . '.');
					if (empty($user->getIrcAccount()))
						Queue::fromContainer($container)
							->privmsg($user->getNickname(),
								'I cannot identify you per your network services account, therefore my functionality may be limited.');
					else
						Queue::fromContainer($container)
							->privmsg($user->getNickname(),
								'I have identified you to be ' . $user->getIrcAccount() . ' based on your network services account, and will use this account for identification purposes in the future.');

					$channelNames = [];
					foreach ($user->getChannelCollection()
						         ->toArray() as $channel)
					{
						$channelNames[] = $channel->getName();
					}
					$channelCount = count($channelNames);
					Queue::fromContainer($container)
						->privmsg($user->getNickname(), 'I can see you in ' . $channelCount . ' channels, namely:');
					Queue::fromContainer($container)
						->privmsg($user->getNickname(), implode(', ', $channelNames));

					if (!empty($user->getIrcAccount()))
					{
						$userGroups = PermissionGroupCollection::fromContainer($container)
							->findAllGroupsForIrcAccount($user->getIrcAccount());

						$groups = [];
						foreach ($userGroups->toArray() as $userGroup)
						{
							$groups[] = $userGroup->getName();
						}

						Queue::fromContainer($container)
							->privmsg($user->getNickname(),
								'You are in the following permission groups: ' . implode(', ', $groups));

						if (Configuration::fromContainer($container)
								->get('owner')
								->getValue() == $user->getIrcAccount()
						)
							Queue::fromContainer($container)
								->privmsg($user->getNickname(),
									'Additionally, you are my owner! I thank my existence to you.');
					}

					Queue::fromContainer($container)
						->privmsg($user->getNickname(), 'That is about it, then!');
				});
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function testQuit(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$result = Validator::fromContainer($container)
			->isAllowedTo('quit', $user, $source);
		if (!$result)
		{
			return;
		}
		Queue::fromContainer($container)
			->quit('Quit command given by ' . $user->getNickname());
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function lscommandsCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$commands = array_keys(CommandHandler::fromContainer($container)
			->getCommandDictionary()
			->toArray());
		$commands = implode(', ', $commands);
		Queue::fromContainer($container)
			->privmsg($source->getName(), 'Available commands: ' . $commands);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function helpCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		if (empty($args))
		{
			$args[0] = 'help';
			$args[1] = '1';
		}

		$command = $args[0];
		$page = !empty($args[1]) ? $args[1] : 1; // Take into account arrays starting at position 0.

		if (!CommandHandler::fromContainer($container)
			->getCommandDictionary()
			->keyExists($command)
		)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'That command does not exist, sorry!');

			return;
		}

		$commandObject = CommandHandler::fromContainer($container)
			->getCommandDictionary()[$command];
		$helpObject = $commandObject->getHelp();
		if ($helpObject == null || !($helpObject instanceof CommandHelp))
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'There is no help available for this command.');

			return;
		}

		$pageToGet = $page - 1;
		if (!$helpObject->indexExists($pageToGet))
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'That page does not exist for this command.');
			Logger::fromContainer($container)
				->debug('Tried to grab invalid page from CommandHelp object.',
					[
						'page' => $pageToGet,
						'object' => $helpObject
					]);

			return;
		}

		$contents = $helpObject->getPageAt($pageToGet);
		$pageCount = $helpObject->getPageCount();
		Queue::fromContainer($container)
			->privmsg($source->getName(), $command . ': ' . $contents . ' (page ' . $page . ' of ' . $pageCount . ')');
	}
}