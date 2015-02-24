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

class CoreCommands
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
		$this->evman = $this->bot->getEventManager();

		// Register our command.
		$this->evman->registerEvent(array('command_quit', 'command_say'), array('hook_once' => true));
		$this->evman->registerEventListener('command_quit', array($this, 'QuitCommand'));
		$this->evman->registerEventListener('command_say', array($this, 'SayCommand'));

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
	 * The Quit command.
	 * @param array $data The data received.
	 */
	public function QuitCommand($data)
	{
		$this->bot->stop(!empty($data['command_arguments']) ? $data['command_arguments'] : null);
	}

	/**
	 * The Say command.
	 * @param array $data The data received.
	 */
	public function SayCommand($data)
	{
		if(substr($data['command_arguments'], 0, 1) == '#')
		{
			$args = explode(' ', $data['command_arguments'], 2);
			$to = $args[0];
			$message = $args[1];
		}
		else
		{
			$to = $data['arguments'][0];
			$message = $data['command_arguments'];
		}

		$this->bot->say($to, $message);
	}
}