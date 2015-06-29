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

class Dev extends BaseModule
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
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our command.
		$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'execCommand'));

		// Get the auth module in here.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * Executes a command.
	 * @param CommandPRIVMSG $e The data received.
	 * @return bool
	 */
	public function execCommand($e)
	{
		if ($e->getCommand() != 'exec' || empty($e->getParams()) || !$this->auth->authUser($e->getSender()))
			return false;

		$this->bot->log('Running command "' . implode(' ', $e->getParams()) . '"');
		eval(implode(' ', $e->getParams()));
		return true;
	}
}