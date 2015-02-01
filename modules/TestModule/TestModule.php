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
	static $dependencies = array('Auth');
	private $bot;

	private $auth;
	public function __construct($bot)
	{
		$this->bot = $bot;

		// Register our command.
		$this->bot->registerEvent(array('command_test', 'command_exec'), array('hook_once' => true));
		$this->bot->hookEvent('command_test', array($this, 'TestCommand'));
		$this->bot->hookEvent('command_exec', array($this, 'ExecCommand'));

		// Get the auth module in here.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	public function TestCommand($data)
	{
		$this->bot->say($data['argument'], 'Test');
	}

	public function ExecCommand($data)
	{
		if (!$this->auth->authUser($data['hostname']))
		{
			$this->bot->say('You are not authorized to execute this command.');
			return false;
		}
		$this->bot->log('Running command "' . $data['string'] . '"');
		eval($data['string']);
	}

	private function createCommand($command, $function)
	{
		try
		{
			$this->bot->registerEvent('command_' . $command, array('hook_once' => true));
			$this->bot->hookEvent('command_' . $command, $function);
		}
		catch (\Exception $e)
		{
			$this->bot->log('An error occurred while adding the command: ' . $e->getMessage());
		}
	}
	private function removeCommand($command)
	{
		$this->bot->unhookEvent('command_' . $command);
	}
}
