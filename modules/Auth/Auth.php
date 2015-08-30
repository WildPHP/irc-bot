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
use WildPHP\IRC\Commands\Privmsg;

/**
 * Class Auth
 *
 * @package WildPHP\Modules
 */
class Auth extends BaseModule
{
	/**
	 * List of hostnames to accept. Boolean false on failure.
	 *
	 * @var string[]|boolean
	 */
	private $hostnames;

	/**
	 * Setup this module.
	 *
	 * @throws \Exception
	 */
	public function setup()
	{
		$this->forceHostnamesReload();

		if ($this->hostnames === false)
			throw new \Exception('Could not read trusted hostnames from the bot config.');
	}

	/**
	 * Checks if this user is authenticated.
	 *
	 * @param string  $hostname The hostname to check.
	 * @param boolean $notify   Notify the user on authentication failure. Defaults to true.
	 * @return boolean
	 */
	public function authUser($hostname, $notify = true)
	{
		// Remove the nickname from the hostname to also match with that.
		$result = $this->isAllowed($hostname);

		$this->log('[AUTH] Checking authorization for hostname {hostname}: ' . ($result ? 'Authorized' : 'Unauthorized'), ['hostname' => $hostname], LogLevels::DEBUG);
		if (!$result && !empty($notify))
		{
			try
			{
				$this->sendData(new Privmsg($this->getLastChannel(), 'You do not have permission to access that.'));
			} // We catch the InvalidArgumentException here, because we might not be able to send a message to a 'last channel'.
			catch (\InvalidArgumentException $e)
			{
			}
		}
		return $result;
	}

	/**
	 * Checks if a hostname is allowed to perform an administrative action.
	 *
	 * @param string $hostname The hostname to check.
	 * @return boolean
	 */
	public function isAllowed($hostname)
	{
		if (empty($hostname) || !is_string($hostname))
			throw new \InvalidArgumentException('No valid argument passed to Auth::isAllowed.');

		// Chop off the nickname.
		$hostnonick = preg_replace('/(:)[a-zA-Z0-9_\-\\\[\]\{\}\^`\|]+\!/', '', $hostname);

		return in_array($hostname, $this->hostnames) || (!empty($hostnonick) && in_array($hostnonick, $this->hostnames));
	}

	/**
	 * Forces a hostname reload.
	 */
	public function forceHostnamesReload()
	{
		$this->hostnames = $this->getConfig('hosts');
	}
}
