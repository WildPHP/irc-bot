<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

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

namespace WildPHP\Core\Connection;

use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\SocketClient\ConnectorInterface;
use React\Stream\Stream;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\ConfigurationItem;

class IrcConnection
{
	/**
	 * @var Promise
	 */
	protected $connectorPromise;

	/**
	 * @var string
	 */
	protected $buffer = '';

	/**
	 * @var ComponentContainer
	 */
	protected $container;

	/**
	 * @param LoopInterface $loop
	 * @param QueueInterface $queue
	 */
	public function registerQueueFlusher(LoopInterface $loop, QueueInterface $queue)
	{
		$loop->addPeriodicTimer(0.5, function () use ($queue)
		{
			$queueItems = $queue->flush();

			foreach ($queueItems as $item)
			{
				$this->write($item->getCommandObject()->formatMessage());
			}
		});
	}

	public function __construct(ComponentContainer $container)
	{
		$container->getEventEmitter()->on('stream.data.in', [$this, 'convertDataToLines']);

		$container->getEventEmitter()->on('irc.line.in.005', [$this, 'handleServerConfig']);

		$container->getEventEmitter()->on('irc.line.in.error', function ()
		{
			$this->close();
		});

		$container->getEventEmitter()->on('irc.force.close', function ()
		{
			$this->close();
		});

		$this->setContainer($container);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 */
	public function handleServerConfig(IncomingIrcMessage $incomingIrcMessage)
	{
		$args = $incomingIrcMessage->getArgs();

		$hostname = $incomingIrcMessage->getPrefix();
		$this->getContainer()->getConfiguration()->set(new ConfigurationItem('serverConfig.hostname', $hostname));

		// The first argument is the nickname set.
		$currentNickname = (string) $args[0];
		$this->getContainer()->getConfiguration()->set(new ConfigurationItem('currentNickname', $currentNickname));
		unset($args[0]);
		$this->getContainer()->getLogger()->debug('Set current nickname to configuration key currentNickname', [$currentNickname]);

		// The last argument is a message usually corresponding to something like "are supported by this server"
		// Don't need that anymore.
		array_pop($args);

		foreach ($args as $value)
		{
			$parts = explode('=', $value);
			$key = 'serverConfig.' . strtolower($parts[0]);
			$value = !empty($parts[1]) ? $parts[1] : true;

			$configItem = new ConfigurationItem($key, $value);
			$this->getContainer()->getConfiguration()->set($configItem);
		}

		$this->getContainer()->getLogger()->debug('Set new server configuration to configuration serverConfig.', [$this->getContainer()->getConfiguration()->get('serverConfig')]);
	}

	/**
	 * @param string $data
	 */
	public function convertDataToLines(string $data)
	{
		// Prepend the buffer, first.
		$data = $this->getBuffer() . $data;

		// Try to split by any combination of \r\n, \r, \n
		$lines = preg_split("/\\r\\n|\\r|\\n/", $data);

		// The last element of this array is always residue.
		$residue = array_pop($lines);
		$this->setBuffer($residue);

		foreach ($lines as $line)
		{
			$this->getContainer()->getLogger()->debug('<< ' . $line);
			$this->getContainer()->getEventEmitter()->emit('stream.line.in', [$line]);
		}
	}

	/**
	 * @param ConnectorInterface $connectorInterface
	 * @param string $host
	 * @param int $port
	 */
	public function createFromConnector(ConnectorInterface $connectorInterface, string $host, int $port)
	{
		$this->connectorPromise = $connectorInterface->create($host, $port)
			->then(function (Stream $stream) use ($host, $port, &$buffer)
			{
				$this->getContainer()->getEventEmitter()->emit('stream.created', [$this->getContainer()->getQueue()]);
				$stream->on('error', function ($error) use ($host, $port)
				{
					throw new \ErrorException('Connection to host ' . $host . ':' . $port . ' failed: ' . $error);
				});

				$stream->on('data', function ($data)
				{
					$this->getContainer()->getEventEmitter()->emit('stream.data.in', [$data]);
				});

				return $stream;
			});
	}

	/**
	 * @param string $data
	 */
	public function write(string $data)
	{
		$this->connectorPromise->then(function (Stream $stream) use ($data)
		{
			$this->getContainer()->getEventEmitter()->emit('stream.data.out', [$data]);
			$this->getContainer()->getLogger()->debug('>> ' . $data);
			$stream->write($data);
		});
	}

	public function close()
	{
		$this->connectorPromise->then(function (Stream $stream)
		{
			$this->getContainer()->getLogger()->warning('Closing connection...');
			$stream->close();
			$this->getContainer()->getEventEmitter()->emit('stream.closed');
		});
	}

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}

	/**
	 * @return string
	 */
	public function getBuffer(): string
	{
		return $this->buffer;
	}

	/**
	 * @param string $buffer
	 */
	public function setBuffer(string $buffer)
	{
		$this->buffer = $buffer;
	}
}