<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ComponentTrait;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\ConfigurationItem;
use WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;

class IrcConnection
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * @var Promise
	 */
	protected $connectorPromise;

	/**
	 * @var string
	 */
	protected $buffer = '';

	/**
	 * @param LoopInterface $loop
	 * @param QueueInterface $queue
	 */
	public function registerQueueFlusher(LoopInterface $loop, QueueInterface $queue)
	{
		$loop->addPeriodicTimer(0.5,
			function () use ($queue)
			{
				$queueItems = $queue->flush();

				/** @var QueueItem $item */
				foreach ($queueItems as $item)
				{
					$verb = strtolower($item->getCommandObject()::getVerb());

					EventEmitter::fromContainer($this->getContainer())
						->emit('irc.line.out', [$item, $this->getContainer()]);

					EventEmitter::fromContainer($this->getContainer())
						->emit('irc.line.out.' . $verb, [$item, $this->getContainer()]);

					if (!$item->isCancelled());
						$this->write($item->getCommandObject());
				}
			});
	}

	/**
	 * IrcConnection constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		EventEmitter::fromContainer($container)
			->on('stream.data.in', [$this, 'convertDataToLines']);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.005', [$this, 'handleServerConfig']);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.error',
				function ()
				{
					$this->close();
				});

		EventEmitter::fromContainer($container)
			->on('irc.force.close',
				function ()
				{
					$this->close();
				});

		$this->setContainer($container);
	}

	/**
	 * @param RPL_ISUPPORT $incomingIrcMessage
	 */
	public function handleServerConfig(RPL_ISUPPORT $incomingIrcMessage)
	{
		$hostname = $incomingIrcMessage->getServer();
		Configuration::fromContainer($this->getContainer())
			->set(new ConfigurationItem('serverConfig.hostname', $hostname));

		// The first argument is the nickname set.
		$currentNickname = $incomingIrcMessage->getNickname();
		Configuration::fromContainer($this->getContainer())
			->set(new ConfigurationItem('currentNickname', $currentNickname));

		Logger::fromContainer($this->getContainer())
			->debug('Set current nickname to configuration key currentNickname', [$currentNickname]);

		$variables = $incomingIrcMessage->getVariables();

		foreach ($variables as $value)
		{
			$parts = explode('=', $value);
			$key = 'serverConfig.' . strtolower($parts[0]);
			$value = !empty($parts[1]) ? $parts[1] : true;

			$configItem = new ConfigurationItem($key, $value);
			Configuration::fromContainer($this->getContainer())
				->set($configItem);
		}

		Logger::fromContainer($this->getContainer())
			->debug('Set new server configuration to configuration serverConfig.',
				[Configuration::fromContainer($this->getContainer())
					->get('serverConfig')]);
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
			Logger::fromContainer($this->getContainer())
				->debug('<< ' . $line);
			EventEmitter::fromContainer($this->getContainer())
				->emit('stream.line.in', [$line]);
		}
	}

	/**
	 * @param ConnectorInterface $connectorInterface
	 * @param string $host
	 * @param int $port
	 */
	public function createFromConnector(ConnectorInterface $connectorInterface, string $host, int $port)
	{
		$this->connectorPromise = $connectorInterface->connect($host . ':' . $port)
			->then(function (ConnectionInterface $connectionInterface) use ($host, $port, &$buffer)
			{
				EventEmitter::fromContainer($this->getContainer())
					->emit('stream.created', [Queue::fromContainer($this->getContainer())]);
				$connectionInterface->on('error',
					function ($error) use ($host, $port)
					{
						throw new \ErrorException('Connection to host ' . $host . ':' . $port . ' failed: ' . $error);
					});

				$connectionInterface->on('data',
					function ($data)
					{
						EventEmitter::fromContainer($this->getContainer())
							->emit('stream.data.in', [$data]);
					});

				return $connectionInterface;
			});
	}

	/**
	 * @param string $data
	 */
	public function write(string $data)
	{
		$this->connectorPromise->then(function (ConnectionInterface $stream) use ($data)
		{
			EventEmitter::fromContainer($this->getContainer())
				->emit('stream.data.out', [$data]);

			Logger::fromContainer($this->getContainer())
				->debug('>> ' . $data);
			$stream->write($data);
		});
	}

	public function close()
	{
		$this->connectorPromise->then(function (ConnectionInterface $stream)
		{
			Logger::fromContainer($this->getContainer())
				->warning('Closing connection...');
			$stream->close();
			EventEmitter::fromContainer($this->getContainer())
				->emit('stream.closed');
		});
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