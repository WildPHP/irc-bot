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

use WildPHP\Bot;
use WildPHP\EventManager\RegisteredEvent;
use WildPHP\Manager;
use WildPHP\Event\IRCMessageInboundEvent;
use WildPHP\Event\IRCMessageOutgoingEvent;
use WildPHP\IRC\ServerMessage;
use WildPHP\IRC\MessageLengthException;
use RuntimeException;

class ConnectionManager extends Manager
{
	const STREAM_TRIM_CHARACTERS = " \t\0\x0B";

	/**
	 * The server you want to connect to.
	 * @var string
	 */
	private $server = '';

	/**
	 * The port of the server you want to connect to.
	 * @var integer
	 */
	private $port = 0;

	/**
	 * The TCP/IP connection.
	 * @var resource
	 */
	protected $socket;

	/**
	 * The password used for connecting.
	 * @var string
	 */
	private $password = '';

	private $name = '';
	private $nick = '';

	/**
	 * The last data received.
	 * @var ServerMessage
	 */
	protected $lastdata = array();

	/**
	 * Set up some initial events.
	 * @param Bot The bot object.
	 */
	public function __construct(Bot $bot)
	{
		$this->bot = $bot;

		$IRCMessageInboundEvent = new RegisteredEvent('IIRCMessageInboundEvent');
		$bot->getEventManager()->register('IRCMessageInbound', $IRCMessageInboundEvent);

		$IRCMessageOutgoingEvent = new RegisteredEvent('IIRCMessageOutgoingEvent');
		$bot->getEventManager()->register('IRCMessageOutgoing', $IRCMessageOutgoingEvent);
	}

	/**
	 * Close the connection.
	 */
	public function __destruct()
	{
		if($this->isConnected())
			$this->disconnect();
	}

	/**
	 * Establishs the connection to the server. If no arguments passed, will use the defaults.
	 * @return boolean|null True or false, depending on whether the connection succeeded.
	 */
	public function connect()
	{
		// Open a connection.
		$this->socket = stream_socket_client($this->server . ':' . $this->port, $errno, $errstr);
		if(!$this->isConnected())
			throw new ConnectionException('Unable to connect to server via fsockopen with server: "' . $this->server . '" and port: "' . $this->port . '" (' . $errno .  ': ' . $errstr . ')');

		socket_set_blocking($this->socket, 0);

		if(!empty($this->password))
			$this->sendData('PASS ' . $this->password);

		$this->sendData('USER ' . $this->nick . ' Layne-Obserdia.de ' . $this->nick . ' :' . $this->name);
		$this->sendData('NICK ' . $this->nick);

		$this->log('Connection to server ' . $this->server . ':' . $this->port . ' set up with nick ' . $this->nick . '; ready to use.');
	}

	/**
	 * Disconnects from the server.
	 *
	 * @return boolean True if the connection was closed. False otherwise.
	 */
	public function disconnect()
	{
		if($this->isConnected())
			return fclose($this->socket);
		return false;
	}

	public function reconnect()
	{
		$this->disconnect();
		$this->connect();
	}

	/**
	 * Sends raw data to the server.
	 * This method makes sure that the message ends with proper EOL characters.
	 *
	 * @param string $data
	 * @return int the number of bytes written.
	 * @throws MessageLengthException when $data exceed maximum lenght.
	 * @throws ConnectionException on socket write error.
	 */
	public function sendData($data)
	{
		$data = trim($data);
		if(strlen($data) > 510)
			throw new MessageLengthException('The data that were supposed to be sent to the server exceed the maximum length of 512 bytes. The data lost were: ' . $data);

		$numBytes = fwrite($this->socket, $data . "\r\n");
		if($numBytes === false)
		{
			$errno = socket_last_error();
			throw new ConnectionException('Writing to socket failed unexpectadly. Error code ' . $errno . ' (' . socket_strerror($errno) . ').');
		}

		// Trigger a new Outgoing event.
		$this->bot->getEventManager()->getEvent('IRCMessageOutgoing')->trigger(
			new IRCMessageOutgoingEvent(new ServerMessage($data))
		);

		return $numBytes;
	}

	/**
	 * Extracts a line from the connected data stream.
	 *
	 * @return null|string The extracted line (trimmed but with line enging characters) or NULL.
	 */
	protected function getData()
	{
		$read = array($this->socket);
		$write = NULL;
		$except = NULL;
		$changed = stream_select($read, $write, $except, 1);
		if ($changed === false)
			return null;

		if($changed > 0)
			return trim(fgets($read[0]), self::STREAM_TRIM_CHARACTERS);
		else
			return null;
	}

	/**
	 * Looks for new data, parses them and triggers an event with the data.
	 *
	 * @return bool False when there were no data to process, true otherwise.
	 */
	public function processReceivedData()
	{
		$data = $this->getData();

		if($data === null)
			return false;

		$this->logDebug('<< ' . $data);

		$data = new ServerMessage($data);

		$this->lastdata = $data;

		// This triggers the event with new ServerMessage as the event data. ServerMessage also does the parsing.
		$this->bot->getEventManager()->getEvent('IRCMessageInbound')->trigger(
			new IRCMessageInboundEvent(
				$data
			)
		);

		return true;
	}

	/**
	 * Check wether the connection exists.
	 *
	 * @return boolean True if the connection exists. False otherwise.
	 */
	public function isConnected()
	{
		return is_resource($this->socket);
	}

	/**
	 * Sets the server.
	 * E.g. irc.quakenet.org or irc.freenode.org
	 * @param string $server The server to set.
	 */
	public function setServer($server)
	{
		$this->server = (string) $server;
	}

	/**
	 * Sets the port.
	 * E.g. 6667
	 * @param integer $port The port to set.
	 */
	public function setPort($port)
	{
		$this->port = (int) $port;
	}

	/**
	 * Set the password used for connecting.
	 * @param string $pass The password to set.
	 */
	public function setPassword($pass)
	{
		$this->password = (string) $pass;
	}

	/**
	 * Set the hostname used for connecting.
	 * @param string $name The hostname to set.
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
	}

	/**
	 * Set the nick used for connecting.
	 * @param string $nick The nickname to set.
	 */
	public function setNick($nick)
	{
		$this->nick = (string) $nick;
	}

	/**
	 * Returns the last data received.
	 * @return ServerMessage
	 */
	public function getLastData()
	{
		return $this->lastdata;
	}
}
