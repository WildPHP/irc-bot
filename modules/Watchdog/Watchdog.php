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

namespace WildPHP\Modules;

use WildPHP\BaseModule;
use WildPHP\LogManager\LogLevels;
use WildPHP\Modules\Watchdog\PongCommand;
use WildPHP\Timer\Timer;
use WildPHP\Event\IRCMessageInboundEvent;
use WildPHP\Event\IRCMessageOutgoingEvent;

class Watchdog extends BaseModule
{
	const TIMEOUT = 350;
	/**
	 * Last ping request.
	 * @var int
	 */
	protected $lastActivity = 0;

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our listeners.
		$this->getEventManager()->getEvent('IRCMessageInbound')->registerListener(array($this, 'pingListener'));
		$this->getEventManager()->getEvent('IRCMessageOutgoing')->registerListener(array($this, 'activityListener'));

		// Register a timer to check for ping timeouts.
		$this->getTimerManager()->add('PingTimeoutTimer', new Timer(self::TIMEOUT, array($this, 'pingTimer')));
	}

	/**
	 * Respond to PING messages
	 * @param IRCMessageInboundEvent $e The last data received.
	 */
	public function pingListener($e)
	{
		if ($e->getMessage()->getCommand() != 'PING')
			return;

		$this->sendData(new PongCommand(substr($e->getMessage()->getMessage(), 5)));
		$this->lastActivity = time();
	}

	/**
	 * Update the internal timer when activity occurs.
	 * @param IRCMessageOutgoingEvent $e The data that is going to be sent.
	 */
	public function activityListener($e)
	{
		$this->lastActivity = time();
	}

	/**
	 * Checks if ping timed out.
	 * @param Timer $e The timer.
	 */
	public function pingTimer($e)
	{
		if ($this->lastActivity + self::TIMEOUT < time())
		{
			// Ping timed out. Reconnecting time!
			$this->log('Ping timeout detected. Attempting to reconnect.', array(), LogLevels::WARNING);
			$this->getConnectionManager()->reconnect();
		}

		// Check back in another round.
		$e->extend(self::TIMEOUT);
	}

}
