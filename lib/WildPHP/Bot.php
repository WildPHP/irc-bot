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

use Evenement\EventEmitter;
use Monolog\Logger;
use Phergie\Irc\Connection;
use Phergie\Irc\ConnectionInterface;
use WildPHP\Configuration\ConfigurationStorage;
use WildPHP\Connection\DataProcessor;
use WildPHP\Connection\StreamFactory;
use WildPHP\Traits\ConfigurationTrait;
use WildPHP\Traits\EventEmitterTrait;
use WildPHP\Traits\LoggerTrait;
use WildPHP\Traits\LoopTrait;
use WildPHP\Traits\StreamTrait;

/**
 * The main bot class. Creates a single bot instance.
 */
class Bot
{
	use ConfigurationTrait;
	use EventEmitterTrait;
	use LoggerTrait;
	use LoopTrait;
	use StreamTrait;

	/**
	 * Loads configuration, sets up a connection and loads modules.
	 *
	 * @param string $configFile
	 */
	public function __construct($configFile = WPHP_CONFIG)
	{
		// Setup the logger.
		
		$this->setLogger();
		$this->setEventEmitter(new EventEmitter());
		$this->setConfigurationStorage(new ConfigurationStorage($configFile));


		// Connect using the given data.
		$connection = new Connection();
		$connection->setServerHostname($this->getConfigurationStorage()->get('server'))
			->setServerPort($this->getConfigurationStorage()->get('port'))
			->setNickname($this->getConfigurationStorage()->get('nick'))
			->setUsername($this->getConfigurationStorage()->get('name'))
			->setRealname('A WildPHP Bot');
	}

	/**
	 * Connects the bot to the given connection.
	 *
	 * @param ConnectionInterface $connection
	 */
	public function connect(ConnectionInterface $connection)
	{
		$factory = new StreamFactory($this->getLoop());

		if ($this->getConfigurationStorage()->get('secure') == true)
			$stream = $factory->createSecure($connection->getServerHostname(), $connection->getServerPort());
		else
			$stream = $factory->create($connection->getServerHostname(), $connection->getServerPort());

		$this->setStream($stream);

		// We do not need to store this object anywhere; it has no benefit to store it.
		new DataProcessor($stream, $this->getEventEmitter());
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		$this->getLoop()->run();
	}
}
