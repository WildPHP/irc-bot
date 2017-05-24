<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\User;

class HelpCommand
{
	/**
	 * HelpCommand constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the help pages for a specific command. (use the lscommands command to list available commands) ' .
			'Usage: help [command] [page]');
		CommandHandler::fromContainer($container)
			->registerCommand('help', [$this, 'helpCommand'], $commandHelp, 0, 2);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows the list of available commands. No arguments.');
		CommandHandler::fromContainer($container)
			->registerCommand('lscommands', [$this, 'lscommandsCommand'], $commandHelp, 0, 0);
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

		$commands = array_chunk($commands, 10);

		foreach ($commands as $key => $commandList)
		{
			$readableCommands = implode(', ', $commandList);
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': Available commands: ' . $readableCommands);
		}
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

		if (count($args) == 1)
		{
			$args[1] = $args[0];
			$args[0] = 'help';
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

		/** @var Command $commandObject */
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