<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class HelpCommand extends BaseModule
{
	/**
	 * HelpCommand constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		CommandHandler::fromContainer($container)->registerCommand('cmdhelp',
			new Command(
				[$this, 'helpCommand'],
				new ParameterDefinitions(0, 1, [
					'command' => new StringParameter()
				]),
				new CommandHelp([
					'Shows the help pages for a specific command. (use the lscommands command to list available commands)',
					'Usage: cmdhelp [command]'
				])
			));

		CommandHandler::fromContainer($container)->registerCommand('lscommands',
			new Command(
				[$this, 'lscommandsCommand'],
				new ParameterDefinitions(0, 0),
				new CommandHelp([
					'Shows the list of available commands. No arguments.'
				])
			));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function lscommandsCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$commands = CommandHandler::fromContainer($container)
			->getCommandCollection()
			->keys();

		$commands = implode(', ', $commands);
		$commands = explode("\n", wordwrap($commands, 200));

		foreach ($commands as $key => $commandList)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': Available commands: ' . $commandList);
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
			$args['command'] = 'cmdhelp';

		$command = $args['command'];

		if (!CommandHandler::fromContainer($container)->getCommandCollection()->offsetExists($command))
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'That command does not exist, sorry!');

			return;
		}

		/** @var Command $commandObject */
		$commandObject = CommandHandler::fromContainer($container)
			->getCommandCollection()[$command];

		$helpObject = clone $commandObject->getHelp();
		if ($helpObject == null || !($helpObject instanceof CommandHelp))
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'There is no help available for this command.');

			return;
		}
		
		/*if (!empty($commandObject->getAliasCollection()->getArrayCopy()))
			$helpObject->append('Aliases: ' . implode(', ', $commandObject->getAliasCollection()->getArrayCopy()));*/
		
		foreach ($helpObject->getIterator() as $page)
		{
			Queue::fromContainer($container)->privmsg($source->getName(), $command . ': ' . $page);
		}
	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}