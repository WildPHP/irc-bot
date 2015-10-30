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

namespace WildPHP\CoreModules\Connection;

use Phergie\Irc\Generator;
use Phergie\Irc\GeneratorInterface;
use Phergie\Irc\Parser;
use Phergie\Irc\ParserInterface;
use React\SocketClient\ConnectorInterface;
use React\Stream\Stream;
use WildPHP\BaseModule;

class Connection extends BaseModule
{
	/**
	 * @var string
	 */
	protected $partial = '';

	/**
	 * @var ParserInterface
	 */
	protected $parser = null;

	/**
	 * @var GeneratorInterface
	 */
	protected $generator = null;

	/**
	 * @var ConnectorInterface
	 */
	protected $connector;

	public function setup()
	{
		$this->setParser(new Parser());
		$this->setGenerator(new Generator());

		$events = [
			'create'          => 'wildphp.init.after',
			'parseData'       => 'irc.data.raw.in',
			'sendInitialData' => 'irc.connection.created',
			'pingPong'        => 'irc.data.in.ping'
		];

		foreach ($events as $function => $event)
		{
			$this->getEventEmitter()->on($event, [$this, $function]);
		}
	}

	public function create()
	{
		$configuration = $this->getModulePool()->get('Configuration');

		$connection = new \Phergie\Irc\Connection();
		$connection->setServerHostname($configuration->get('server'))
			->setServerPort($configuration->get('port'))
			->setNickname($configuration->get('nick'))
			->setUsername($configuration->get('name'))
			->setRealname('A WildPHP Bot');

		$factory = new ConnectorFactory($this->getLoop());

		if ($configuration->get('secure'))
			$connector = $factory->createSecure($configuration->get('server'), $configuration->get('port'));
		else
			$connector = $factory->create($configuration->get('server'), $configuration->get('port'));

		$connector->then(function (Stream $stream) use ($connector)
		{
			$stream->on('data', function ($data) use ($connector)
			{
				$this->getEventEmitter()->emit('irc.data.raw.in', [$data, $connector]);
			});

			$this->getEventEmitter()->emit('irc.connection.created');

			return $stream;
		});

		$this->connector = $connector;
	}

	/**
	 * @param string $data
	 */
	public function parseData($data)
	{
		$consumable = $this->partial . $data;

		// Consume all valid messages and leave partial ones behind.
		$messages = $this->getParser()->consumeAll($consumable);
		$this->partial = $consumable;

		foreach ($messages as $message)
		{
			$this->getEventEmitter()->emit('irc.data.in', [$message]);

			if (!empty($message['command']))
				$this->getEventEmitter()->emit('irc.data.in.' . strtolower($message['command']), [$message]);
		}
	}

	/**
	 * @param array    $data
	 */
	public function pingPong($data)
	{
		$this->write($this->getGenerator()->ircPong($data['params']['server1']));
	}

	/**
	 * @return ParserInterface
	 */
	public function getParser()
	{
		return $this->parser;
	}

	/**
	 * @param ParserInterface $parser
	 */
	protected function setParser(ParserInterface $parser)
	{
		$this->parser = $parser;
	}

	public function sendInitialData()
	{
		$configuration = $this->getModulePool()->get('Configuration');
		$this->write($this->getGenerator()->ircUser($configuration->get('name'), gethostname(), $configuration->get('name'), 'A WildPHP Bot'));
	}

	/**
	 * @param array $data
	 */
	public function write($data)
	{
		$parsed = $this->getParser()->parse($data);
		if ($parsed == null)
		{
			$this->getModulePool()->get('Logger')->debug('Tried to write invalid IRC data: ' . $data);

			return;
		}

		$this->getConnector()->then(function (Stream $stream) use ($data, $parsed)
		{
			$stream->write($data);

			$this->getEventEmitter()->emit('irc.data.out', [$parsed]);
			$this->getEventEmitter()->emit('irc.data.out.' . strtolower($parsed['command']), [$parsed]);
		});
	}

	/**
	 * @return ConnectorInterface
	 */
	protected function getConnector()
	{
		return $this->connector;
	}

	/**
	 * @param ConnectorInterface $connector
	 */
	protected function setConnector(ConnectorInterface $connector)
	{
		$this->connector = $connector;
	}

	/**
	 * @return GeneratorInterface
	 */
	public function getGenerator()
	{
		return $this->generator;
	}

	/**
	 * @param GeneratorInterface $generator
	 */
	protected function setGenerator(GeneratorInterface $generator)
	{
		$this->generator = $generator;
	}
}