<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\RPL_ISUPPORT;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class IrcConnection implements ComponentInterface
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
	 * @var ConnectionDetails
	 */
	protected $connectionDetails;

	/**
	 * @param ComponentContainer $container
	 * @param ConnectionDetails $connectionDetails
	 */
	public function __construct(ComponentContainer $container, ConnectionDetails $connectionDetails)
	{
		$container->getLoop()
			->addPeriodicTimer(0.5, [$this, 'flushQueue']);

		EventEmitter::fromContainer($container)
			->on('stream.data.in', [$this, 'convertDataToLines']);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.005', [$this, 'handleServerConfig']);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.error', [$this, 'close']);

		EventEmitter::fromContainer($container)
			->on('irc.force.close', [$this, 'close']);

		EventEmitter::fromContainer($container)
			->on('stream.created', [$this, 'sendInitialConnectionDetails']);

		$this->setContainer($container);
		$this->setConnectionDetails($connectionDetails);
	}

	/**
	 * @param Queue $queue
	 */
	public function sendInitialConnectionDetails(Queue $queue)
	{
		$connectionDetails = $this->getConnectionDetails();
		$queue->user(
			$connectionDetails->getUsername(),
			$connectionDetails->getHostname(),
			$connectionDetails->getAddress(),
			$connectionDetails->getRealname()
		);
		$queue->nick($connectionDetails->getWantedNickname());

		if (!empty($connectionDetails->getPassword()))
			$queue->pass($connectionDetails->getPassword());
	}

	public function flushQueue()
	{
		$queueItems = Queue::fromContainer($this->getContainer())->flush();

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
	}

	/**
	 * @param RPL_ISUPPORT $incomingIrcMessage
	 */
	public function handleServerConfig(RPL_ISUPPORT $incomingIrcMessage)
	{
		$hostname = $incomingIrcMessage->getServer();
		Configuration::fromContainer($this->getContainer())['serverConfig']['hostname'] = $hostname;

		// The first argument is the nickname set.
		$currentNickname = $incomingIrcMessage->getNickname();
		Configuration::fromContainer($this->getContainer())['currentNickname'] = $currentNickname;

		Logger::fromContainer($this->getContainer())
			->debug('Set current nickname to configuration key currentNickname', [$currentNickname]);

		$variables = $incomingIrcMessage->getVariables();

		foreach ($variables as $value)
		{
			$parts = explode('=', $value);
			$key = strtolower($parts[0]);
			$value = !empty($parts[1]) ? $parts[1] : true;
			Configuration::fromContainer($this->getContainer())['serverConfig'][$key] = $value;
		}

		Logger::fromContainer($this->getContainer())
			->debug('Set new server configuration to configuration serverConfig.',
				[Configuration::fromContainer($this->getContainer())['serverConfig']]);
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
	 *
	 * @return Promise|\React\Promise\PromiseInterface
	 */
	public function connect(ConnectorInterface $connectorInterface)
	{
		$connectionString = $this->getConnectionDetails()->getAddress() . ':' . $this->getConnectionDetails()->getPort();
		$promise = $connectorInterface->connect($connectionString)
			->then(function (ConnectionInterface $connectionInterface) use (&$buffer, $connectionString)
			{
				EventEmitter::fromContainer($this->getContainer())
					->emit('stream.created', [Queue::fromContainer($this->getContainer())]);

				$connectionInterface->on('error',
					function ($error) use ($connectionString)
					{
						throw new ConnectionException('Connection to ' . $connectionString . ' failed: ' . $error);
					});

				$connectionInterface->on('data',
					function ($data)
					{
						EventEmitter::fromContainer($this->getContainer())
							->emit('stream.data.in', [$data]);
					});

				return $connectionInterface;
			});

		$this->connectorPromise = $promise;
		return $promise;
	}

	/**
	 * @param string $data
	 *
	 * @return \React\Promise\PromiseInterface
	 */
	public function write(string $data)
	{
		$promise = $this->connectorPromise->then(function (ConnectionInterface $stream) use ($data)
		{
			EventEmitter::fromContainer($this->getContainer())
				->emit('stream.data.out', [$data]);

			Logger::fromContainer($this->getContainer())
				->debug('>> ' . $data);
			$stream->write($data);
		});

		return $promise;
	}

	/**
	 * @return \React\Promise\PromiseInterface
	 */
	public function close()
	{
		$promise = $this->connectorPromise->then(function (ConnectionInterface $stream)
		{
			Logger::fromContainer($this->getContainer())
				->warning('Closing connection...');
			$stream->close();
			EventEmitter::fromContainer($this->getContainer())
				->emit('stream.closed');
		});

		return $promise;
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

	/**
	 * @return ConnectionDetails
	 */
	public function getConnectionDetails(): ConnectionDetails
	{
		return $this->connectionDetails;
	}

	/**
	 * @param ConnectionDetails $connectionDetails
	 */
	public function setConnectionDetails(ConnectionDetails $connectionDetails)
	{
		$this->connectionDetails = $connectionDetails;
	}
}