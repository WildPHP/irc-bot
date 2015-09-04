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

namespace WildPHP\Connection;

use Phergie\Irc\ConnectionInterface;
use React\SocketClient\Connector;
use React\Stream\Stream;
use WildPHP\Api;

class IrcConnection
{
	/**
	 * The API.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Connector object.
	 *
	 * @var Connector
	 */
	protected $connector;

	/**
	 * Stream object.
	 *
	 * @var Stream
	 */
	protected $stream;

	/**
	 * The connection details.
	 *
	 * @var ConnectionInterface
	 */
	protected $connectionDetails;

	/**
	 * Partial message from last data process. It happens.
	 *
	 * @var string
	 */
	protected $partial = '';

	/**
	 * @return ConnectionInterface
	 */
	public function getConnectionDetails()
	{
		return $this->connectionDetails;
	}

	/**
	 * @param ConnectionInterface $connectionDetails
	 */
	public function setConnectionDetails(ConnectionInterface $connectionDetails)
	{
		$this->connectionDetails = $connectionDetails;
	}

	/**
	 * @return Connector
	 */
	public function getConnector()
	{
		if (!$this->connector)
		{
			$loop = $this->api->getLoop();
			$this->connector = new Connector($loop, $this->api->getResolver());
		}

		return $this->connector;
	}

	/**
	 * @param Connector $secureConnector
	 */
	public function setConnector(Connector $secureConnector)
	{
		$this->connector = $secureConnector;
	}

	/**
	 * Inject the API.
	 *
	 * @param Api $api
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @param ConnectionInterface $connection
	 */
	public function create(ConnectionInterface $connection)
	{
		if ($this->stream)
			$this->disconnect();

		$hostname = $connection->getServerHostname();
		$port = $connection->getServerPort();

		$this->getConnector()->create($hostname, $port)->then(function (Stream $stream) use ($connection)
		{
			$this->setStream($stream);
			$this->setConnectionDetails($connection);
			$this->api->getEmitter()->emit('irc.connect', [$stream, $connection]);
			$this->write($this->api->getGenerator()->ircNick($connection->getNickname()));
			$this->write($this->api->getGenerator()->ircUser($connection->getNickname(), gethostname(), $connection->getNickname(), $connection->getUsername()));
			$stream->on('data', [$this, 'processData']);
		});
	}

	/**
	 * Process incoming data.
	 *
	 * @param string $data
	 */
	public function processData($data)
	{
		$all = $this->partial . $data;
		$messages = $this->api->getParser()->consumeAll($all);
		$this->partial = $all;

		foreach ($messages as $message)
		{
			$this->api->getLogger()->debug('<< ' . $message['message']);

			// Fire both a generic irc.data.in and an irc.data.in.{command} event.
			$this->api->getEmitter()->emit('irc.data.in', [$message]);
			if (!empty($message['command']))
				$this->api->getEmitter()->emit('irc.data.in.' . strtolower($message['command']), [$message]);
		}
	}

	/**
	 * Start up the stream.
	 *
	 * @param Stream $stream
	 */
	public function setStream(Stream $stream)
	{
		$this->stream = $stream;
	}

	/**
	 * Writes data to the socket and makes sure it is logged.
	 *
	 * @param string $data The data to log.
	 */
	public function write($data)
	{
		$parsed = $this->api->getParser()->parse($data);
		if (empty($parsed))
		{
			$this->api->getLogger()->warning('Malformed outgoing message: ' . $data);

			return;
		}
		$this->api->getLogger()->info('>> ' . $data);
		$this->stream->write($data);

		// Trigger a new irc.data.out event.
		$this->api->getEmitter()->emit('irc.data.out', [$parsed]);
		$this->api->getEmitter()->emit('irc.data.out.' . strtolower($parsed['command']), [$parsed]);
	}

	/**
	 * Called when the bot received data.
	 *
	 * @param string $data The data received.
	 */
	public function receive($data)
	{
		$this->api->getLogger()->info('<< ' . $data);
	}

	/**
	 * Disconnect the bot from IRC.
	 */
	public function disconnect()
	{
		$this->write($this->api->getGenerator()->ircQuit());
		$this->stream->close();
	}
}