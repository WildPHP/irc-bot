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

use WildPHP\Configuration\ConfigurationManager;
use WildPHP\Connection\ConnectionManager;
use WildPHP\EventManager\EventManager;
use WildPHP\EventManager\RegisteredEvent;

/**
 * The main bot class. Creates a single bot instance.
 */
class Bot
{
	/**
	 * The configuration manager.
	 * @var ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * The module manager.
	 * @var ModuleManager
	 */
	protected $moduleManager;

	/**
	 * The event manager.
	 * @var EventManager
	 */
	protected $eventManager;

	/**
	 * The connection manager.
	 * @var ConnectionManager
	 */
	protected $connectionManager;

	/**
	 * The log manager.
	 * @var LogManager
	 */
	protected $log;

	/**
	 * The database object.
	 * @var \SQLite3
	 */
	public $db;

	/**
	 * Sets up the bot for initial load.
	 * @param string $configFile Optionally load a custom config file
	 */
	public function __construct($configFile = WPHP_CONFIG)
	{

		// Load the configuration in memory.
		$this->configurationManager = new ConfigurationManager($this, $configFile);

		// Plug in the log.
		$this->log = new LogManager($this);
		register_shutdown_function(array($this->log, 'logShutdown'));

		// Then set up the database.
		$this->db = new \SQLite3($this->configurationManager->get('database'));

		// And we'd like an event manager.
		$this->eventManager = new EventManager($this);

		// Register some default events.
		$IRCMessageInboundEvent = new RegisteredEvent('IIRCMessageInboundEvent');
		$this->eventManager->register('IRCMessageInbound', $IRCMessageInboundEvent);

		// Ping handler
		$IRCMessageInboundEvent->registerEventHandler(
			function($e)
			{
				if($e->getCommand() === 'PING')
					$this->sendData('PONG ' . substr($e->getMessage(), 5));
			}
		);

		// And fire up any existing modules.
		$this->moduleManager = new ModuleManager($this);
		$this->moduleManager->setup();

		// Set up a connection.
		$this->connectionManager = new ConnectionManager($this);
	}

	/**
	 * Set up the connection for the bot.
	 */
	public function connect()
	{
		// For that, we need to set the connection parameters.
		// First up, server.
		$this->connectionManager->setServer($this->configurationManager->get('server'));
		$this->connectionManager->setPort($this->configurationManager->get('port'));

		// Then we insert the details for the bot.
		$this->connectionManager->setNick($this->configurationManager->get('nick'));
		$this->connectionManager->setName($this->configurationManager->get('nick'));

		// Optionally, a password, too.
		$this->connectionManager->setPassword($this->configurationManager->get('password'));

		// And start the connection.
		$this->connectionManager->connect();

		// !!! onConnect event
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		while($this->connectionManager->isConnected())
		{
			$this->connectionManager->processReceivedData();
		}
	}

	/**
	 * Returns an item stored in the configuration.
	 * @param string $item The configuration item to get.
	 * @return mixed The item stored called by key, or false on failure.
	 */
	public function getConfig($item)
	{
		return $this->configurationManager->get($item);
	}

	/**
	 * Returns a module.
	 * @param string $module The module to get an instance from.
	 * @return object|false The module instance on success, false on failure.
	 */
	public function getModuleInstance($module)
	{
		return $this->moduleManager->getModuleInstance($module);
	}

	/**
	 * Returns the EventManager.
	 * @return EventManager The Event Manager.
	 */
	public function getEventManager()
	{
		return $this->eventManager;
	}

	/**
	 * Returns the ModuleManager.
	 * @return ModuleManager The Module Manager.
	 */
	public function getModuleManager()
	{
		return $this->moduleManager;
	}

	/**
	 * Send data to the remote.
	 * @param string $data The data to send.
	 */
	public function sendData($data)
	{
		$this->log($data, 'DATAOUT');
		$this->connectionManager->sendData($data);
	}

	/**
	 * Say something to a channel.
	 * @param string $to The channel to send to, or, if one parameter passed, the text to send to the current channel.
	 * @param mixed $text The string to be sent or an array of strings. Newlines separate messages.
	 * @return bool False on failure, true on success.
	 */
	public function say($to, $text = '')
	{
		if(empty($to) && empty($text))
			return false;

		// Some people are just too lazy.
		elseif(empty($text) && $this->lastData['command'] == 'PRIVMSG' && !empty($this->lastData['arguments'][0]))
		{
			$text = $to;
			$to = $this->lastData['arguments'][0];
		}

		// !!! onSay event

		// Nothing to send?
		if(empty($text) || empty($to))
			return false;

		// Split multiple lines into separate messages *for each member of the input array* (or string, possibly)
		// Also removes empty lines and other garbage and splits the line if it's too long
		$out = array();
		foreach((array) $text as $part)
		{
			$part = (string) $part;
			$part = preg_replace('/[\n\r]+/', "\n", $part);

			$lines = explode("\n", (string) $part);
			foreach($lines as $lines2)
			{
				// We have the line we could potentially send. That's nice but it can be too long, so there is another split
				// The maximum without the last CRLF is 510 characters, minus the PRIVMSG stuff (10 chars) gives us something like this:
				$lines2 = str_split($lines2, 510 - 10 - strlen($to));
				foreach($lines2 as $line)
				{
					// We finally have the correct line
					$line = trim($line);
					if(!empty($line))
						array_push($out, $line);
				}
			}
		}

		foreach($out as $msg)
			$this->sendData('PRIVMSG ' . $to . ' :' . $msg);

		return true;
	}

	/**
	 * Log data.
	 * @param string $data  The data to log.
	 * @param string $level The level to log the data at; can be anything.
	 */
	public function log($data, $level = 'LOG')
	{
		$this->log->log($data, $level);
	}

	/**
	 * Disconnects the bot and stops.
	 * @param string $message Send a custom message along with the QUIT command.
	 */
	public function stop($message = 'WildPHP <http://wildphp.com/>')
	{
		if(empty($message))
			$message = 'WildPHP <http://wildphp.com/>';

		$this->sendData('QUIT :' . $message);
		$this->connectionManager->disconnect();
		exit;
	}

	/**
	 * Fetches data from $uri
	 * @param string $uri    The URI to fetch data from.
	 * @param bool   $decode Whether to attempt to decode the received data using json_decode.
	 * @return mixed Returns a string if $decode is set to false. Returns an array if json_decode succeeded, or false if it failed.
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

		if(!empty($decode) && ($output = json_decode($output)) === null)
			$output = false;

		// close curl resource to free up system resources
		curl_close($ch);
		return $output;
	}
}
