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
use WildPHP\Modules\CommandParser\Event\CommandEvent;
use WildPHP\IRC\Commands\Privmsg;
use WildPHP\LogManager\LogLevels;

class Dev extends BaseModule
{
	/**
	 * Register our commands.
	 * @return array
	 */
	public function registerCommands()
	{
		return [
			'exec' => [
				'callback' => 'execCommand',
				'help' => 'Executes PHP code in the bot\'s process. Usage: exec [code]',
				'auth' => true
			]
		];
	}

	/**
	 * Executes a command.
	 *
	 * @param CommandEvent $e The data received.
	 */
	public function execCommand($e)
	{
		if (empty($e->getParams()))
		{
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: exec [code to execute]'));
			return;
		}

		$this->log('Running command "{command}"', ['command' => implode(' ', $e->getParams())], LogLevels::INFO);
		eval(implode(' ', $e->getParams()));
	}
}
