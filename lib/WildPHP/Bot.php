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
use Phergie\Irc\Connection;
use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\GeneratorInterface;
use Phergie\Irc\ParserInterface;
use Psr\Log\LoggerInterface;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use WildPHP\Configuration\ConfigurationStorage;
use WildPHP\Connection\IrcConnection;

/**
 * The main bot class. Creates a single bot instance.
 */
class Bot
{
	/**
	 * The Api instance.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * The IrcConnection.
	 *
	 * @var IrcConnection
	 */
	protected $ircConnection;

	/**
	 * Loads all modules.
	 *
	 * @param string $configFile The configuration file to use for this bot instance.
	 */
	public function __construct($configFile = WPHP_CONFIG)
	{
		$configurationStorage = new ConfigurationStorage($configFile);
		$this->getApi()->setConfigurationStorage($configurationStorage);
		$this->getApi()->getModuleEmitter();

		// Connect using the given data.
		$connection = new Connection();
		$connection->setServerHostname($configurationStorage->get('server'))
			->setServerPort($configurationStorage->get('port'))
			->setNickname($configurationStorage->get('nick'))
			->setUsername($configurationStorage->get('name'))
			->setRealname('A WildPHP Bot');
		$this->connect($connection);
	}

	/**
	 * @return IrcConnection
	 */
	public function getIrcConnection()
	{
		if (!$this->ircConnection)
			$this->setIrcConnection(new IrcConnection($this->getApi()));

		return $this->ircConnection;
	}

	/**
	 * @param IrcConnection $ircConnection
	 */
	public function setIrcConnection(IrcConnection $ircConnection)
	{
		$this->ircConnection = $ircConnection;
		$this->api->setIrcConnection($ircConnection);
	}

	/**
	 * @return GeneratorInterface
	 */
	public function getGenerator()
	{
		return $this->getApi()->getGenerator();
	}

	/**
	 * @param GeneratorInterface $generator
	 */
	public function setGenerator(GeneratorInterface $generator)
	{
		$this->getApi()->setGenerator($generator);
	}

	/**
	 * @return ParserInterface
	 */
	public function getParser()
	{
		return $this->getApi()->getParser();
	}

	/**
	 * @param ParserInterface $parser
	 */
	public function setParser(ParserInterface $parser)
	{
		$this->getApi()->setParser($parser);
	}

	/**
	 * @return Resolver
	 */
	public function getResolver()
	{
		return $this->getApi()->getResolver();
	}

	/**
	 * @param Resolver $resolver
	 */
	public function setResolver(Resolver $resolver)
	{
		$this->getApi()->setResolver($resolver);
	}

	/**
	 * @return Api
	 */
	public function getApi()
	{
		if (!$this->api)
			$this->setApi(new Api());

		return $this->api;
	}

	/**
	 * @param Api $api
	 */
	public function setApi(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @return EventEmitter
	 */
	public function getEmitter()
	{
		return $this->getApi()->getEmitter();
	}

	/**
	 * @param EventEmitter $emitter
	 */
	public function setEmitter(EventEmitter $emitter)
	{
		$this->getApi()->setEmitter($emitter);
	}

	/**
	 * Returns the loop interface.
	 *
	 * @return \React\EventLoop\LoopInterface
	 */
	public function getLoop()
	{
		return $this->getApi()->getLoop();
	}

	/**
	 * @return \Psr\Log\LoggerInterface
	 */
	public function getLogger()
	{
		return $this->getApi()->getLogger();
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->getApi()->setLogger($logger);
	}

	/**
	 * Sets the loop interface.
	 *
	 * @param \React\EventLoop\LoopInterface $loop
	 */
	public function setLoop(LoopInterface $loop)
	{
		$this->getApi()->setLoop($loop);
	}

	/**
	 * Connects the bot to the given connection.
	 *
	 * @param ConnectionInterface $connection
	 */
	public function connect(ConnectionInterface $connection)
	{
		$this->getIrcConnection()->create($connection);
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		$this->getLoop()->run();
	}
}
