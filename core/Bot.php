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

namespace WildPHP\Core;

/**
 * The main bot class. Creates a single bot instance.
 */
class Bot
{
	/**
	 * The configuration manager.
	 * @var \WildPHP\Core\Configuration
	 */
	protected $configuration;

	/**
	 * The module manager.
	 * @var \WildPHP\Core\ModuleManager
	 */
	protected $moduleManager;

	/**
	 * The event manager.
	 * @var \WildPHP\Core\EventManager
	 */
	protected $eventManager;

	/**
	 * The connection manager.
	 * @var \WildPHP\Core\ConnectionManager
	 */
	protected $connection;

	/**
	 * The log manager.
	 * @var \WildPHP\Core\LogManager
	 */
	protected $log;

	/**
	 * The IRCParser.
	 * @var \IRCParser\IRCParser
	 */
	protected $parser;

	/**
	 * The last data received.
	 * @var array
	 */
	public $lastData;

	/**
	 * The database object.
	 * @var \SQLite3
	 */
	public $db;

	/**
	 * Sets up the bot for initial load.
	 * @param string $config_file Optionally load a custom config file
	 */
	public function __construct($config_file = WPHP_CONFIG)
	{

		// Load the configuration in memory.
		$this->configuration = new Configuration($this, $config_file);

		// Plug in the log.
		$this->log = new LogManager($this);
		register_shutdown_function(array($this->log, 'logShutdown'));

		// Then set up the database.
		$this->db = new \SQLite3($this->configuration->get('database'));

		// And we'd like an event manager.
		$this->eventManager = new EventManager($this);

		// Register some default events.
		$this->eventManager->registerEvent(array('onConnect', 'onSay'));
		$this->eventManager->registerEvent('onDataReceive', array('surpress_log' => true));

		// And fire up any existing modules.
		$this->moduleManager = new ModuleManager($this);
		$this->moduleManager->setup();

		// Set up a connection.
		$this->connection = new ConnectionManager($this);

		// And the parser.
		$this->parser = new \IRCParser\IRCParser($this);
	}

	/**
	 * Set up the connection for the bot.
	 */
	public function connect()
	{
		// For that, we need to set the connection parameters.
		// First up, server.
		$this->connection->setServer($this->configuration->get('server'));
		$this->connection->setPort($this->configuration->get('port'));

		// Then we insert the details for the bot.
		$this->connection->setNick($this->configuration->get('nick'));
		$this->connection->setName($this->configuration->get('nick'));

		// Optionally, a password, too.
		$this->connection->setPassword($this->configuration->get('password'));

		// And start the connection.
		$this->connection->connect();

		// Call the connection hook.
		$this->eventManager->triggerEvent('onConnect');
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		if (!$this->connection->isConnected())
			throw new \Exception('No connection has been set up for the bot to use.');

		do
		{
			// Check if we got any new data. Signs of life!
			$data = $this->connection->getData();
			if (empty($data))
				continue;

			// Make a note of what we received.
			$this->log($data, 'DATA');

			// Parse the data.
			$data = $this->parser->process($data);

			// Got a PING? Do PONG. Probably nothing needs to handle this anyway. Plus we skip cycles worrying about nothing.
			if ($data['command'] == 'PING')
			{
				$this->sendData('PONG ' . $data['arguments'][0]);
				continue;
			}

			// Set the data so we can use it elsewhere.
			$this->lastData = $data;

			// Got a command?
			if (!empty($data['bot_command']) && $this->eventManager->eventExists('command_' . $data['bot_command']))
				$this->eventManager->triggerEvent('command_' . $data['bot_command'], $data);

			$this->eventManager->triggerEvent('onDataReceive', $data);
		}
		while ($this->connection->isConnected());
	}

	/**
	 * Returns an item stored in the configuration.
	 * @return mixed The item stored called by key, or false on failure.
	 */
	public function getConfig($item)
	{
		return $this->configuration->get($item);
	}

	/**
	 * Returns an instance of a module.
	 * @param string $module The module to get an instance from.
	 * @return object|false The module instance on success, false on failure.
	 */
	function getModuleInstance($module)
	{
		return $this->moduleManager->getModuleInstance($module);
	}

	/**
	 * Returns an instance of the EventManager.
	 * @return \WildPHP\Core\EventManager The Event Manager.
	 */
	public function getEventManager()
	{
		return $this->eventManager;
	}

	/**
	 * Returns an instance of the ModuleManager.
	 * @return \WildPHP\Core\ModuleManager The Module Manager.
	 */
	public function getModuleManager()
	{
		return $this->moduleManager;
	}

	/**
	 * Returns an instance of the IRCParser class.
	 * @return \IRCParser\IRCParser The IRCParser.
	 */
	public function getIRCParser()
	{
		return $this->parser;
	}

	/**
	 * Send data to the remote.
	 * @param string $data The data to send.
	 */
	public function sendData($data)
	{
		$this->log($data, 'DATAOUT');
		$this->connection->sendData($data);
	}

	/**
	 * Say something to a channel.
	 * @param string $to The channel to send to, or, if one parameter passed, the text to send to the current channel.
	 * @param string $text The text to send.
	 * @return bool False on failure, true on success.
	 */
	public function say($to, $text = '')
	{
		if (empty($to) && empty($text))
			return false;

		// Some people are just too lazy.
		elseif (empty($text) && $this->lastData['command'] == 'PRIVMSG' && !empty($this->lastData['arguments'][0]))
		{
			$text = $to;
			$to = $this->lastData['arguments'][0];
		}

		// Nothing to send?
		if (empty($text) || empty($to))
			return false;

		$this->eventManager->triggerEvent('onSay', array('to' => $to, 'text' => &$text));

		$this->sendData('PRIVMSG ' . $to . ' :' . $text);
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
		$this->sendData('QUIT :' . $message);
		$this->connection->disconnect();
		exit;
	}

	/**
	 * Fetches data from $uri
	 * @param string $uri    The URI to fetch data from.
	 * @param bool   $decode Whether to attempt to decode the received data using json_decode.
	 * @return mixed Returns a string if $decode is set to false. Returns an array if json_decode succeeded, or false if it failed.
	 */
	public static function fetch($uri, $decode = false) {
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
}
