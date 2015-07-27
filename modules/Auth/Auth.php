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
use WildPHP\LogManager\LogLevels;

class Auth extends BaseModule
{
	/**
	 * List of hostnames to accept. Boolean false on failure.
	 * @var string[]|boolean
	 */
	private $hostnames;

	public function setup()
	{
		$this->forceHostnamesReload();
		
		if ($this->hostnames === false)
			throw new \Exception('Could not read trusted hostnames from the bot config.');
	}

	/**
	 * Checks if this user is authenticated.
	 * @param string $hostname The hostname to check.
	 * @param boolean $notify Notify the user on authentication failure. Defaults to true.
	 * @return boolean
	 */
	public function authUser($hostname, $notify = true)
	{
		// Remove the nickname from the hostname to also match with that. The nickname doesn't have to always be the same!
		$hostnonick = preg_replace('/(:)[a-zA-Z0-9_\-\\\[\]\{\}\^`\|]+\!/', '', $hostname);
		$result = !empty($hostname) && (in_array($hostname, $this->hostnames) || in_array($hostnonick, $this->hostnames));

		$this->bot->log('Checking authorization for hostname {hostname}: ' . ($result ? 'Authorized' : 'Unauthorized'), array('hostname' => $hostname), LogLevels::INFO);
		
		if (!$result && !empty($notify))
		{
			// Need to do it like this, we might not have a PRIVMSG as last data!
			try
			{
				$this->bot->say('You do not have permission to access that.');
			}
			catch (\InvalidArgumentException $e) {}
		}

		return $result;
	}
	
	/**
	 * Forces a hostname reload.
	 */
	public function forceHostnamesReload()
	{
		$this->hostnames = $this->bot->getConfig('hosts');
	}
}
