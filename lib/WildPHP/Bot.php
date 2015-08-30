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
use WildPHP\Connection\QueueManager;
use WildPHP\EventManager\ModuleCrashedException;
use WildPHP\LogManager\LogManager;
use WildPHP\LogManager\LogLevels;
use WildPHP\EventManager\EventManager;
use WildPHP\EventManager\RegisteredEvent;

/**
 * The main bot class. Creates a single bot instance.
 */
class Bot
{
	/**
	 * The configuration manager.
	 *
	 * @var ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * The module manager.
	 *
	 * @var ModuleManager
	 */
	protected $moduleManager;

	/**
	 * The event manager.
	 *
	 * @var EventManager
	 */
	protected $eventManager;

	/**
	 * The connection manager.
	 *
	 * @var ConnectionManager
	 */
	protected $connectionManager;

	/**
	 * The TimerManager
	 *
	 * @var TimerManager
	 */
	protected $timerManager;

	/**
	 * The Queue manager.
	 *
	 * @var QueueManager
	 */
	protected $queueManager;

	/**
	 * The log manager.
	 *
	 * @var LogManager
	 */
	protected $logManager;

	/**
	 * The database object. TODO
	 *
	 * @var \SQLite3
	 */
	public $db;

	/**
	 * The current nickname of the bot.
	 *
	 * @var string
	 */
	protected $nickname;

	/**
	 * Sets up the bot for initial load.
	 *
	 * @param string $configFile Optionally load a custom config file
	 */
	public function __construct($configFile = WPHP_CONFIG)
	{
		// Set up all managers.
		$this->initializeManagers($configFile);

		$this->nickname = $this->getConfig('nick');

		// Then set up the database.
		$this->db = new \SQLite3($this->getConfig('database'));

		$this->getEventManager()->getEvent('BotCommand')->setAuthModule($this->getModuleManager()->getModuleInstance('Auth'));
	}

	/**
	 * Get all managers locked and loaded.
	 *
	 * @param string $configFile The config file to load.
	 */
	protected function initializeManagers($configFile)
	{
		$this->configurationManager = new ConfigurationManager($this, $configFile);
		date_default_timezone_set($this->getConfig('timezone'));

		$this->logManager = new LogManager($this);
		new ErrorHandler($this);

		$this->eventManager = new EventManager($this);
		$this->initializeEvents();

		$this->timerManager = new TimerManager($this);

		$this->connectionManager = new ConnectionManager($this);

		$this->queueManager = new QueueManager($this, $this->getConfig('flood.linespersecond'), $this->getConfig('flood.burst'));

		$this->moduleManager = new ModuleManager($this);
		$this->moduleManager->setup();
	}

	/**
	 * Initialize all core events.
	 */
	public function initializeEvents()
	{
		// Loop - Triggered at every iteration of the bot's main loop.
		$LoopEvent = new RegisteredEvent('IEvent', $this->getEventManager());
		$this->getEventManager()->register('Loop', $LoopEvent);
	}

	/**
	 * Set up the connection for the bot.
	 */
	public function connect()
	{
		// Pass over the server and port details.
		$this->getConnectionManager()->setServer($this->getConfig('server'));
		$this->getConnectionManager()->setPort($this->getConfig('port'));

		// Then we insert the details for the bot.
		$this->getConnectionManager()->setNick($this->getConfig('nick'));
		$this->getConnectionManager()->setName($this->getConfig('nick'));

		// Optionally, a password, too.
		$this->getConnectionManager()->setPassword($this->getConfig('password'));

		// Start the connection.
		$this->getConnectionManager()->connect();
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		while ($this->getConnectionManager()->isConnected())
		{
			try
			{
				// Let anything hook into the main loop for its own business.
				$this->getEventManager()->getEvent('Loop')->trigger(new Event\LoopEvent());
				$this->getConnectionManager()->processReceivedData();
			}
			catch (ModuleCrashedException $e)
			{
				// Oh dear. A module crashed.
				$this->getModuleManager()->kickByObject($e->getModule());
			}
		}
	}

	/**
	 * Returns an item stored in the configuration.
	 *
	 * @param string $item The configuration item to get.
	 * @return false|mixed The item stored called by key, or false on failure.
	 */
	public function getConfig($item)
	{
		return $this->configurationManager->get($item);
	}

	/**
	 * Returns the Connection Manager
	 *
	 * @return ConnectionManager The Connection Manager
	 */
	public function getConnectionManager()
	{
		return $this->connectionManager;
	}

	/**
	 * Returns the EventManager.
	 *
	 * @return EventManager The Event Manager.
	 */
	public function getEventManager()
	{
		return $this->eventManager;
	}

	/**
	 * Returns the Timer Manager
	 *
	 * @returns TimerManager The Timer Manager.
	 */
	public function getTimerManager()
	{
		return $this->timerManager;
	}

	/**
	 * Returns the ModuleManager.
	 *
	 * @return ModuleManager The Module Manager.
	 */
	public function getModuleManager()
	{
		return $this->moduleManager;
	}

	/**
	 * Gets the current nickname.
	 *
	 * @return string
	 */
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * Change the nickname of the bot.
	 *
	 * @param string $newnick
	 * @return boolean True on success, false on failure.
	 */
	public function changeNickname($newnick)
	{
		if (empty($newnick))
			return false;

		$this->getConnectionManager()->send('NICK ' . $newnick);

		$data = $this->getConnectionManager()->waitReply();
		if (!empty($data))
		{
			if (!empty($data[0]->get()['code']) && in_array($data[0]->get()['code'], ['ERR_NICKNAMEINUSE', 'ERR_ERRONEUSNICKNAME', 'ERR_NICKCOLLISION']))
				return false;
		}

		$this->nickname = $newnick;

		return true;
	}

	/**
	 * Set the nickname of the bot. Please try to use changeNickname instead.
	 *
	 * @param string $newnick
	 */
	public function setNickname($newnick)
	{
		$this->nickname = $newnick;
	}

	/**
	 * Log data.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context to use.
	 * @param string $level   The level to log the data at.
	 */
	public function log($message, $context = [], $level = LogLevels::DEBUG)
	{
		call_user_func([$this->logManager, $level], $message, $context);
	}

	/**
	 * Disconnects the bot and stops.
	 *
	 * @param string $message Send a custom message along with the QUIT command.
	 */
	public function stop($message = 'WildPHP <http://wildphp.com/>')
	{
		if (empty($message))
			$message = 'WildPHP <http://wildphp.com/>';

		$this->getConnectionManager()->send('QUIT :' . $message);
		$this->getConnectionManager()->disconnect();
		exit;
	}
}
