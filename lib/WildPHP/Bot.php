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
use WildPHP\Event\IRCMessageInboundEvent;
use WildPHP\IRC\ServerMessage;
use WildPHP\LogManager\LogManager;
use WildPHP\LogManager\LogLevels;
use WildPHP\EventManager\EventManager;
use WildPHP\EventManager\RegisteredEvent;
use WildPHP\EventManager\RegisteredCommandEvent;
use WildPHP\Event\SayEvent;

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
	 * The TimerManager
	 * @var TimerManager
	 */
	protected $timerManager;

	/**
	 * The Queue manager.
	 * @var QueueManager
	 */
	protected $queueManager;

	/**
	 * The log manager.
	 * @var LogManager
	 */
	protected $logManager;

	/**
	 * The database object.
	 * @var \SQLite3
	 */
	public $db;

	/**
	 * The current nickname of the bot.
	 * @var string
	 */
	protected $nickname;

	/**
	 * Sets up the bot for initial load.
	 * @param string $configFile Optionally load a custom config file
	 */
	public function __construct($configFile = WPHP_CONFIG)
	{
		// Set up all managers.
		$this->initializeManagers($configFile);

		$this->nickname = $this->getConfig('nick');

		// Then set up the database.
		$this->db = new \SQLite3($this->getConfig('database'));

		$this->getEventManager()->getEvent('IRCMessageInbound')->registerEventHandler(
			function(IRCMessageInboundEvent $e)
			{
				if ($e->getMessage()->getCommand() != 'PRIVMSG')
					return;

				$msg = new IRC\CommandPRIVMSG($e->getMessage(), $this->getConfig('prefix'));

				if ($msg->getBotCommand() === false)
					return;

				$this->getEventManager()->getEvent('BotCommand')->trigger(
					new Event\CommandEvent($msg)
				);
			}
		);

		$this->getEventManager()->getEvent('BotCommand')->setAuthModule($this->getModuleInstance('Auth'));
	}

	/**
	 * Get all managers locked and loaded.
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

		// TODO: Make its settings configurable.
		$this->queueManager = new QueueManager($this);

		$this->moduleManager = new ModuleManager($this);
		$this->moduleManager->setup();
	}

	/**
	 * Initialize all core events.
	 */
	public function initializeEvents()
	{
		// BotCommand - Triggered when the bot receives a command from a user.
		$BotCommandEvent = new RegisteredCommandEvent('ICommandEvent');
		$this->getEventManager()->register('BotCommand', $BotCommandEvent);

		// Say - When the bot is going to "say" (PRIVMSG) to a channel.
		$SayEvent = new RegisteredEvent('ISayEvent');
		$this->getEventManager()->register('Say', $SayEvent);

		// Loop - Triggered at every iteration of the bot's main loop.
		$LoopEvent = new RegisteredEvent('IEvent');
		$this->getEventManager()->register('Loop', $LoopEvent);
	}

	/**
	 * Set up the connection for the bot.
	 */
	public function connect()
	{
		// Pass over the server and port details.
		$this->connectionManager->setServer($this->getConfig('server'));
		$this->connectionManager->setPort($this->getConfig('port'));

		// Then we insert the details for the bot.
		$this->connectionManager->setNick($this->getConfig('nick'));
		$this->connectionManager->setName($this->getConfig('nick'));

		// Optionally, a password, too.
		$this->connectionManager->setPassword($this->getConfig('password'));

		// Start the connection.
		$this->connectionManager->connect();
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		while ($this->connectionManager->isConnected())
		{
			// Let anything hook into the main loop for its own business.
			$this->eventManager->getEvent('Loop')->trigger(new Event\LoopEvent());
			$this->connectionManager->processReceivedData();
		}
	}

	/**
	 * Returns an item stored in the configuration.
	 * @param string $item The configuration item to get.
	 * @return false|mixed The item stored called by key, or false on failure.
	 */
	public function getConfig($item)
	{
		return $this->configurationManager->get($item);
	}

	/**
	 * Returns a module.
	 * @param string $module The module to get an instance from.
	 * @return BaseModule The module instance.
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
	 * Returns the Timer Manager
	 * @returns TimerManager The Timer Manager.
	 */
	public function getTimerManager()
	{
		return $this->timerManager;
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
	 * Gets the current nickname.
	 * @return string
	 */
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * Change the nickname of the bot.
	 * @param string $newnick
	 * @return boolean True on success, false on failure.
	 */
	public function changeNickname($newnick)
	{
		if (empty($newnick))
			return false;

		$this->sendData('NICK ' . $newnick);

		$data = $this->waitReply();
		if (!empty($data))
		{
			if (!empty($data[0]->get()['code']) && in_array($data[0]->get()['code'], array('ERR_NICKNAMEINUSE', 'ERR_ERRONEUSNICKNAME', 'ERR_NICKCOLLISION')))
				return false;
		}

		$this->nickname = $newnick;

		return true;
	}

	/**
	 * Set the nickname of the bot. Please try to use changeNickname instead.
	 * @param string $newnick
	 */
	public function setNickname($newnick)
	{
		$this->nickname = $newnick;
	}

	/**
	 * Send data to the remote.
	 * @param string $data The data to send.
	 */
	public function sendData($data)
	{
		$this->connectionManager->sendData($data);
	}

	/**
	 * Gets data from the remote.
	 * @return ServerMessage|false
	 */
	public function getData()
	{
		return $this->connectionManager->processReceivedData();
	}

	/**
	 * Waits for and gets a reply from the server.
	 * THIS HALTS THE TIMERS FOR THE SPECIFIED TIME.
	 * @param int $lines The amount of lines to listen for.
	 * @param int $timeout Timeout for listening to data. Defaults to 3 seconds.
	 * @return ServerMessage[]
	 */
	public function waitReply($lines = 1, $timeout = 3)
	{
		$currtime = time();
		$receivedLines = array();

		do
		{
			$data = $this->getData();

			if ($data == false)
				continue;

			$receivedLines[] = $data;

			if (count($receivedLines) == $lines)
				break;
		} while (count($receivedLines) < $lines && time() < $currtime + $timeout);

		return $receivedLines;
	}

	/**
	 * Say something to a channel.
	 * @param string[]|string $to The channel to send to, or, if one parameter passed, the text to send to the current channel.
	 * @param string $text The string to be sent or an array of strings. Newlines separate messages.
	 * @return bool False on failure (or when cancelled), true on success.
	 * @throws \InvalidArgumentException When you use the shorthand yet no channel data is available.
	 */
	public function say($to, $text = '')
	{
		if (empty($to) && empty($text))
			return false;

		// Some people are just too lazy.
		elseif (empty($text) && $this->connectionManager->getLastData()->getCommand() == 'PRIVMSG')
		{
			$text = (string) $to;
			$to = $this->connectionManager->getLastData()->get()['targets'][0];

			// Are we talking to ourself?
			if ($to == $this->getNickname())
				$to = $this->connectionManager->getLastData()->getNickname();
		}
		elseif (empty($text))
			throw new \InvalidArgumentException('The last data received was NOT a PRIVMSG command and you did not specify a channel to say to.');

		$e = new SayEvent($text, $to);
		$this->eventManager->getEvent('Say')->trigger($e);

		if ($e->isCancelled())
			return false;

		// Nothing to send?
		if (empty($text) || empty($to))
			return false;

		// Split multiple lines into separate messages *for each member of the input array* (or string, possibly)
		// Also removes empty lines and other garbage and splits the line if it's too long
		$out = array();
		foreach ((array) $text as $part)
		{
			$part = (string) $part;
			$part = preg_replace('/[\n\r]+/', "\n", $part);

			$lines = explode("\n", (string) $part);
			foreach ($lines as $lines2)
			{
				// We have the line we could potentially send. That's nice but it can be too long, so there is another split
				// The maximum without the last CRLF is 510 characters, minus the PRIVMSG stuff (10 chars) gives us something like this:
				$lines2 = str_split($lines2, 510 - 10 - strlen($to));
				foreach ($lines2 as $line)
				{
					// We finally have the correct line
					$line = trim($line);
					if (!empty($line))
						array_push($out, $line);
				}
			}
		}

		foreach ($out as $msg)
			$this->sendData('PRIVMSG ' . $to . ' :' . $msg);

		return true;
	}

	/**
	 * Log data.
	 * @param string $message The message to log.
	 * @param array $context The context to use.
	 * @param string $level The level to log the data at.
	 */
	public function log($message, $context = array(), $level = LogLevels::DEBUG)
	{
		call_user_func(array($this->logManager, $level), $message, $context);
	}

	/**
	 * Disconnects the bot and stops.
	 * @param string $message Send a custom message along with the QUIT command.
	 */
	public function stop($message = 'WildPHP <http://wildphp.com/>')
	{
		if (empty($message))
			$message = 'WildPHP <http://wildphp.com/>';

		$this->sendData('QUIT :' . $message);
		$this->connectionManager->disconnect();
		exit;
	}

	/**
	 * Reconnects the bot.
	 */
	public function reconnect()
	{
		$this->connectionManager->reconnect();
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

		if (!empty($decode) && ($output = json_decode($output)) === null)
			$output = false;

		// close curl resource to free up system resources
		curl_close($ch);
		return $output;
	}
}
