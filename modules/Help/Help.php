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

class Help
{
	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var \WildPHP\Core\Bot
	 */
	private $bot;

	/**
	 * The Auth module's object.
	 * @var \WildPHP\Modules\Auth
	 */
	private $auth;

	/**
	 * Set up the module.
	 * @param object $bot The Bot object.
	 */
	public function __construct($bot)
	{
		$this->bot = $bot;

		// Get the event manager over here.
		$evman = $this->bot->getEventManager();

		// Register our command.
		$evman->registerEvent('command_help', array('hook_once' => true));
		$evman->registerEventListener('command_help', array($this, 'HelpCommand'));

		// Get the auth module in here.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * Returns the module dependencies.
	 * @return string[] The array containing the module names of the dependencies.
	 */
	public static function getDependencies()
	{
		return array('Auth');
	}

	/**
	 * The help command itself.
	 * @param array $data The data received.
	 */
	public function HelpCommand($data)
	{
		// Do we have a module for specific help?
		$pieces = explode(' ', $data['command_arguments']);
		$cmd = array_shift($pieces);

		// Nope, show all commands.
		if(empty($cmd))
		{
			// All commands are...
			$cmd = $this->bot->getModuleManager()->getLoadedModules();

			// Yep.
			$this->bot->say('Available modules: ' . implode(', ', array_keys($cmd)));
		}

		// Yep, show the data for the single command, if available.
		else
		{

		}

	}
}
