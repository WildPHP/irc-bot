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
use WildPHP\Validation;
use WildPHP\Event\CommandEvent;

class CoreCommands extends BaseModule
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
	protected static $dependencies = array('Auth', 'Help');

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our command.
		$this->evman()->getEvent('BotCommand')->registerCommand('quit', array($this, 'quitCommand'), true);
		$this->evman()->getEvent('BotCommand')->registerCommand('say', array($this, 'sayCommand'), true);

		$helpmodule = $this->bot->getModuleInstance('Help');
		$helpmodule->registerHelp('quit', 'Shuts down the bot. Usage: quit ([message])');
		$helpmodule->registerHelp('say', 'Makes the bot say something to a channel. Usage: say ([channel]) [message]');

		// Get the auth module in here.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * The Quit command.
	 * @param CommandEvent $e The data received.
	 */
	public function quitCommand($e)
	{
		$this->bot->stop(!empty($e->getParams()) ? implode(' ', $e->getParams()) : null);
	}

	/**
	 * The Say command.
	 * @param CommandEvent $e The data received.
	 */
	public function sayCommand($e)
	{
		if (Validation::isChannel($e->getParams()[0]))
		{
			$args = $e->getParams();
			$to = array_shift($args);
			$message = implode(' ', $args);
		}
		else
		{
			$to = $e->getMessage()->getTargets();
			$message = implode(' ', $e->getParams());
		}

		if ($to === null)
			return;

		$this->bot->say($to, $message);
	}
}
