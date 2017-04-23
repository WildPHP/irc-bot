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
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

class PingPongHandler
{
	/**
	 * @var int
	 */
	protected $lastMessasgeReceived = 0;

	/**
	 * The amount of seconds per time the checking loop is run.
	 * Do not set this too high or the ping handler won't be effective.
	 * @var int
	 */
	protected $loopInterval = 2;

	/**
	 * In seconds.
	 * @var int
	 */
	protected $pingInterval = 120;

	/**
	 * In seconds.
	 * @var int
	 */
	protected $disconnectInterval = 120;

	public function __construct()
	{
		EventEmitter::on('irc.line.in', [$this, 'updateLastMessageReceived']);

		EventEmitter::on('irc.line.in.ping', function (IncomingIrcMessage $incomingIrcMessage, Queue $queue)
		{
			$queue->pong($incomingIrcMessage->getArgs()[0]);
		});
		$this->updateLastMessageReceived();
	}

	public function updateLastMessageReceived()
	{
		$this->lastMessasgeReceived = time();
	}

	/**
	 * @param LoopInterface $loop
	 * @param Queue $queue
	 */
	public function registerPingLoop(LoopInterface $loop, Queue $queue)
	{
		$loop->addPeriodicTimer($this->loopInterval, function () use ($queue)
		{
			$currentTime = time();

			$disconnectTime = $this->lastMessasgeReceived + $this->pingInterval + $this->disconnectInterval;
			$shouldDisconnect = $currentTime >= $disconnectTime;

			if ($shouldDisconnect)
				return $this->forceDisconnect($queue);

			$scheduledPingTime = $this->lastMessasgeReceived + $this->pingInterval;
			$shouldSendPing = $currentTime >= $scheduledPingTime;

			if ($shouldSendPing)
				return $this->sendPing($queue);
		});
	}

	/**
	 * @param Queue $queue
	 *
	 * @return bool
	 */
	public function sendPing(Queue $queue)
	{
		Logger::debug('No message received from the server in the last ' . $this->pingInterval . ' seconds. Sending PING.');
		$server = Configuration::get('serverConfig.hostname')->getValue();
		$queue->ping($server);
		return true;
	}

	/**
	 * @param Queue $queue
	 *
	 * @return bool
	 */
	public function forceDisconnect(Queue $queue)
	{
		Logger::warning('The server has not responded to the last PING command. Is the network down? Closing link.');
		$queue->quit('No vital signs detected, closing link...');
		return true;
	}
}