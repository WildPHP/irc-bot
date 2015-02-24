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

class TestModule
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
		$this->evman->registerEvent(array('command_test'), array('hook_once' => true));
		$this->evman->registerEventListener('command_test', array($this, 'TestCommand'));
		//$this->evman->registerEventListener('command_exec', array($this, 'ExecCommand'));

		$this->evman->registerEventListener('onDataReceive', array($this, 'TestListener'), 'highest');

		// Get the auth module in here.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * Returns the module dependencies.
	 * @return array The array containing the module names of the dependencies.
	 */
	public static function getDependencies()
	{
		return array('Auth');
	}

	public function TestCommand()
	{
		$this->bot->say('Test');
	}

	public function ExecCommand($data)
	{
		if(!$this->auth->authUser($data['hostname']))
		{
			$this->bot->say('You are not authorized to execute this command.');
			return false;
		}
		$this->bot->log('Running command "' . $data['command_arguments'] . '"');
		eval($data['command_arguments']);

		return true;
	}

	public function TestListener($data)
	{
	}
}
