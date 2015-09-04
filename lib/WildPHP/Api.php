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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phergie\Irc\Generator;
use Phergie\Irc\GeneratorInterface;
use Phergie\Irc\ParserInterface;
use Phergie\Irc\Parser;
use React\EventLoop\Factory as LoopFactory;
use React\Dns\Resolver\Resolver;
use WildPHP\Configuration\ConfigurationStorage;
use WildPHP\Connection\IrcConnection;

class Api
{
	/**
	 * The loop.
	 *
	 * @var \React\EventLoop\LoopInterface
	 */
	protected $loop;

	/**
	 * The logger.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * An event emitter.
	 *
	 * @var EventEmitter
	 */
	protected $emitter;

	/**
	 * The DNS resolver.
	 *
	 * @var Resolver
	 */
	protected $resolver;

	/**
	 * IRC Message generator.
	 *
	 * @var GeneratorInterface
	 */
	protected $generator;

	/**
	 * IRC Message parser.
	 *
	 * @var ParserInterface
	 */
	protected $parser;

	/**
	 * The IRC connection.
	 *
	 * @var IrcConnection
	 */
	protected $ircConnection;

	/**
	 * The module manager.
	 *
	 * @var ModuleEmitter
	 */
	protected $moduleEmitter;

	/**
	 * The configuration storage.
	 *
	 * @var ConfigurationStorage
	 */
	protected $configurationStorage;

	/**
	 * @return ConfigurationStorage
	 */
	public function getConfigurationStorage()
	{
		return $this->configurationStorage;
	}

	/**
	 * @param ConfigurationStorage $configurationStorage
	 */
	public function setConfigurationStorage(ConfigurationStorage $configurationStorage)
	{
		$this->configurationStorage = $configurationStorage;
	}

	/**
	 * @return ModuleEmitter
	 */
	public function getModuleEmitter()
	{
		if (!$this->moduleEmitter)
			$this->moduleEmitter = new ModuleEmitter($this);
		return $this->moduleEmitter;
	}

	/**
	 * @param ModuleEmitter $moduleEmitter
	 */
	public function setModuleEmitter(ModuleEmitter $moduleEmitter)
	{
		$this->moduleEmitter = $moduleEmitter;
	}

	/**
	 * @return IrcConnection
	 */
	public function getIrcConnection()
	{
		return $this->ircConnection;
	}

	/**
	 * @param IrcConnection $ircConnection
	 */
	public function setIrcConnection(IrcConnection $ircConnection)
	{
		$this->ircConnection = $ircConnection;
	}

	/**
	 * @return GeneratorInterface
	 */
	public function getGenerator()
	{
		if (!$this->generator)
			$this->setGenerator(new Generator());

		return $this->generator;
	}

	/**
	 * @param GeneratorInterface $generator
	 */
	public function setGenerator(GeneratorInterface $generator)
	{
		$this->generator = $generator;
	}

	/**
	 * @return ParserInterface
	 */
	public function getParser()
	{
		if (!$this->parser)
			$this->setParser(new Parser());

		return $this->parser;
	}

	/**
	 * @param ParserInterface $parser
	 */
	public function setParser(ParserInterface $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * @return Resolver
	 */
	public function getResolver()
	{
		if (!$this->resolver)
		{
			$factory = new \React\Dns\Resolver\Factory();
			$this->setResolver($factory->createCached('8.8.8.8', $this->getLoop()));
		}

		return $this->resolver;
	}

	/**
	 * @param Resolver $resolver
	 */
	public function setResolver($resolver)
	{
		$this->resolver = $resolver;
	}

	/**
	 * @return EventEmitter
	 */
	public function getEmitter()
	{
		if (!$this->emitter)
			$this->setEmitter(new EventEmitter());

		return $this->emitter;
	}

	/**
	 * @param EventEmitter $emitter
	 */
	public function setEmitter($emitter)
	{
		$this->emitter = $emitter;
	}

	/**
	 * Returns the loop interface.
	 *
	 * @return \React\EventLoop\LoopInterface
	 */
	public function getLoop()
	{
		if (!$this->loop)
			$this->setLoop(LoopFactory::create());

		return $this->loop;
	}

	/**
	 * @return \Psr\Log\LoggerInterface
	 */
	public function getLogger()
	{
		// As default we use an external library.
		if (!$this->logger)
		{
			$logger = new Logger('WildPHP');

			$i = 0;
			do
			{
				$i++;
			}
			while (file_exists(WPHP_LOG_DIR . '/log_' . $i . '.log'));

			$logger->pushHandler(new StreamHandler(WPHP_LOG_DIR . '/log_' . $i . '.log'));
			$logger->pushHandler(new StreamHandler('php://stdout'));
			$this->setLogger($logger);
		}

		return $this->logger;
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function setLogger($logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Sets the loop interface.
	 *
	 * @param \React\EventLoop\LoopInterface $loop
	 */
	public function setLoop($loop)
	{
		$this->loop = $loop;
	}

	/**
	 * Fetches data from $uri
	 *
	 * @param string $uri    The URI to fetch data from.
	 * @param bool   $decode Whether to attempt to decode the received data using json_decode.
	 * @return mixed Returns a string if $decode is set to false. Returns an array if json_decode succeeded, or
	 *                       false if it failed.
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