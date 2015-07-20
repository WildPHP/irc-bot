<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

namespace WildPHP\Modules;

use WildPHP\BaseModule;
use WildPHP\IRC\CommandPRIVMSG;

class Help extends BaseModule
{
	/**
	 * The Auth module's object.
	 * @var \WildPHP\Modules\Auth
	 */
	private $auth;

	/**
	 * Dependencies of this module.
	 * @var string[]
	 */
	protected static $dependencies = array('Auth');

	/**
	 * The registered help strings.
	 * @var array<string, string>
	 */
	protected $strings = array();

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our command.
		$this->evman()->getEvent('BotCommand')->registerCommand('help', array($this, 'helpCommand'));
		$this->registerHelp('help', 'You just used it correctly.');

		// Get the auth module in here.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * The help command itself.
	 * @param CommandEvent $e The data received.
	 */
	public function helpCommand($e)
	{
		$commands = $this->evman()->getEvent('BotCommand')->getCommands();

		if (empty($e->getParams()))
		{
			// All commands are...
			$this->bot->say('Available commands: ' . implode(', ', $commands));
		}
		else
		{
			$command = strtolower($e->getParams()[0]);

			if (!in_array($command, $commands))
			{
				$this->bot->say('Command ' . $command . ' does not exist or is not known to me.');
				return;
			}

			if (!array_key_exists($command, $this->strings))
			{
				$this->bot->say('There is no help available for command ' . $command);
				return;
			}

			$this->bot->say($command . ': ' . $this->strings[$command]);
		}
	}

	/**
	 * Register help for a command.
	 * @param string $command The command to set help for.
	 * @param string $string The help string to set for it.
	 * @param boolean $overwrite Overwrite existing help if already set?
	 */
	public function registerHelp($command, $string, $overwrite = false)
	{
		if (empty($command) || empty($string) || !is_string($command) || !is_string($string))
			throw new \InvalidArgumentException();

		$cmd = $this->evman()->getEvent('BotCommand');

		if (!$cmd->commandExists($command))
			throw new HelpForNonexistingCommand();

		if (array_key_exists($command, $this->strings) && !$overwrite)
			throw new HelpAlreadyRegistered();

		$this->strings[strtolower($command)] = $string;
	}
}

class HelpForNonexistingCommand extends \RuntimeException
{
}

class HelpAlreadyRegistered extends \RuntimeException
{
}
