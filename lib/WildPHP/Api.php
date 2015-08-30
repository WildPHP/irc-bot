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

namespace WildPHP;

use WildPHP\Connection\ConnectionManager;
use WildPHP\EventManager\EventManager;
use WildPHP\IRC\IRCData;
use WildPHP\LogManager\LogLevels;

class Api
{
	/**
	 * The bot object.
	 *
	 * @var Bot
	 */
	private $bot = null;

	/**
	 * Set up the module.
	 *
	 * @param Bot $bot The Bot object.
	 */
	public function __construct(Bot $bot)
	{
		$this->setBot($bot);
	}

	/**
	 * Sets the bot object.
	 *
	 * @param Bot $bot
	 */
	private function setBot(Bot $bot)
	{
		$this->bot = $bot;
	}

	/**
	 * Helper function for using the Event Manager.
	 *
	 * @return EventManager
	 */
	public function getEventManager()
	{
		return $this->bot->getEventManager();
	}

	/**
	 * Helper function for using the Timer Manager.
	 *
	 * @return TimerManager
	 */
	public function getTimerManager()
	{
		return $this->bot->getTimerManager();
	}

	/**
	 * Return the connection manager.
	 *
	 * @return ConnectionManager
	 */
	public function getConnectionManager()
	{
		return $this->bot->getConnectionManager();
	}

	/**
	 * Return the module manager.
	 *
	 * @return ModuleManager
	 */
	public function getModuleManager()
	{
		return $this->bot->getModuleManager();
	}

	/**
	 * Gets a module from the module manager.
	 *
	 * @param string $module The module name.
	 * @return BaseModule
	 */
	public function getModule($module)
	{
		return $this->bot->getModuleManager()->getModuleInstance($module);
	}

	/**
	 * Fetches data from $uri
	 *
	 * @param string $uri    The URI to fetch data from.
	 * @param bool   $decode Whether to attempt to decode the received data using json_decode.
	 * @return mixed Returns a string if $decode is set to false. Returns an array if json_decode succeeded, or
	 *               false if it failed.
	 */
	public static function fetch($uri, $decode = false)
	{
		// create curl resource
		$ch = curl_init();

		// set url
		curl_setopt($ch, CURLOPT_URL, $uri);

		// user agent.
		curl_setopt($ch, CURLOPT_USERAGENT, 'WildPHP/IRCBot');

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

		// $output contains the output string
		$output = curl_exec($ch);

		if (!empty($decode) && ($output = json_decode($output)) === null)
			$output = false;

		// close curl resource to free up system resources
		curl_close($ch);
		return $output;
	}

	/**
	 * Returns an item stored in the configuration.
	 *
	 * @param string $item The configuration item to get.
	 * @return false|mixed The item stored called by key, or false on failure.
	 */
	public function getConfig($item)
	{
		return $this->bot->getConfig($item);
	}

	/**
	 * Send data out.
	 *
	 * @param IRCData $data The data to send.
	 */
	public function sendData(IRCData $data)
	{
		if (empty((string)$data))
			return;

		$this->getConnectionManager()->send((string)$data);
	}

	/**
	 * Gets the last channel something was said to.
	 *
	 * @return string|null Null when no data available, string if there is.
	 */
	public function getLastChannel()
	{
		$targets = $this->bot->getConnectionManager()->getLastData()->getTargets();
		return !empty($targets) ? $targets[0] : null;
	}

	/**
	 * Sets the bot nickname.
	 *
	 * @param string $nickname
	 */
	public function setNickname($nickname)
	{
		$this->bot->setNickname($nickname);
	}

	/**
	 * Gets the nickname.
	 *
	 * @return string
	 */
	public function getNickname()
	{
		return $this->bot->getNickname();
	}

	/**
	 * Sends a message to the log.
	 *
	 * @param string $message the message to be logged.
	 * @param array  $context The context to use.
	 * @param string $level   The level to log at. Defaults to debug.
	 */
	public function log($message, $context = [], $level = LogLevels::DEBUG)
	{
		$this->bot->log($message, $context, $level);
	}

	/**
	 * Creates a new Api instance.
	 *
	 * @return Api
	 */
	public function newApiInstance()
	{
		return new Api($this->bot);
	}
}