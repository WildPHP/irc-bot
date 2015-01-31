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

	protected $configuration;
	protected $moduleManager;
	protected $eventManager;
	protected $connection;
	protected $log;
	protected $parser;

	/**
	 * @param string $config_file Optionally load a custom config file
	 */
	public function __construct($config_file = WPHP_CONFIG)
	{
		// Load the configuration in memory.
		$this->configuration = new Configuration($this, $config_file);

		// And we'd like an event manager.
		$this->eventManager = new EventManager($this);

		// Register some default events.
		$this->eventManager->register(array('onConnect', 'onDataReceive'));

		// And fire up any existing modules.
		$this->moduleManager = new ModuleManager($this);

		// Set up a connection.
		$this->connection = new ConnectionManager($this);

		// Plug in the log.
		$this->log = new LogManager($this);
		register_shutdown_function(array($this->log, 'logShutdown'));

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
		$this->eventManager->call('onConnect');
	}

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

			// !!! REMOVE
			if (stripos($data, 'This nickname is registered'))
				$this->sendData('JOIN #NanoPlayground');

			// Parse the data.
			$data = $this->parser->process($data);

			// Got a PING? Do PONG. Probably nothing needs to handle this anyway.
			if ($data['command'] == 'PING')
			{
				$this->sendData('PONG :' . $data['hostname']);
				continue;
			}

			$this->eventManager->call('onDataReceive', $data);
		}
		while (true);
	}

	public function getConfiguration($item)
	{
		return $this->configuration->get($item);
	}

	/**
	 * Event manager getters/setters.
	 */
	public function hookEvent($event, $hook)
	{
		$this->eventManager->hook($event, $hook);
	}

	public function registerEvent($event)
	{
		$this->eventManager->register($event);
	}

	/**
	 * Module manager getters/setters.
	 */
	public function loadModule($module)
	{
		$this->moduleManager->loadModule($module);
	}
	public function unloadModule($module)
	{
		$this->moduleManager->unloadModule($module);
	}

	/**
	 * Connection manager  getters/setters
	 */
	public function sendData($data)
	{
		$this->log($data, 'DATAOUT');
		$this->connection->sendData($data);
	}


	public function log($data, $level = 'LOG')
	{
		$this->log->log($data, $level);
	}
}
