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

use WildPHP\LogManager\LogLevels;
use WildPHP\Timer\Timer;
use WildPHP\Timer\TimerDoesNotExistException;
use WildPHP\Timer\TimerExistsException;

class TimerManager extends Manager
{
	/**
	 * The timers. Stored as array<timestamp, callable>
	 * @var Timer[]
	 */
	protected $timers = array();

	/**
	 * Hook into events and get everything set up.
	 * @param Bot $bot The bot object.
	 */
	public function __construct(Bot $bot)
	{
		parent::__construct($bot);

		$this->bot->getEventManager()->getEvent('Loop')->registerListener(array($this, 'trigger'));
	}

	/**
	 * Adds a timer.
	 * @param string $name The name to set for the timer.
	 * @param Timer $timer The timer object to add.
	 * @throws \InvalidArgumentException when no name is specified or an invalid timer is passed.
	 * @throws TimerExistsException when a timer already exists either by the same name or object.
	 */
	public function add($name, Timer $timer)
	{
		if (!is_object($timer) || !($timer instanceof Timer) || empty($name))
			throw new \InvalidArgumentException('Unable to add timer with invalid parameters.');

		if ($this->exists($name) || $this->existsByObject($timer))
			throw new TimerExistsException('The specified timer already exists.');

		$this->bot->log('Added new timer {name}. Next trigger in {nextTrigger} seconds.', array('name' => $name, 'nextTrigger' => ($timer->getTime() - time())), LogLevels::DEBUG);
		$this->timers[$name] = $timer;
	}

	/**
	 * Checks if a timer exists, searching on the name.
	 * @param string $name
	 * @return boolean
	 */
	public function exists($name)
	{
		return array_key_exists($name, $this->timers);
	}

	/**
	* Checks if a timer exists, searching by object.
	* @param Timer $timer
	* @return boolean
	*/
	public function existsByObject(Timer $timer)
	{
		return in_array($timer, $this->timers);
	}

	/**
	 * Removes a timer by name.
	 * @param string $name The timer to remove.
	 * @throws TimerDoesNotExistException when the timer does not exist.
	 */
	public function remove($name)
	{
		if (!$this->exists($name))
			throw new TimerDoesNotExistException();

		unset($this->timers[$name]);
		$this->bot->log('Removed timer {name}', array('name' => $name), LogLevels::DEBUG);
	}

	/**
	 * Removes a timer by timer object.
	 * @param Timer $timer
	 */
	public function removeByObject(Timer $timer)
	{
		$this->remove(array_search($timer, $this->timers));
	}

	/**
	 * Trigger all timers for this time or past times.
	 */
	public function trigger()
	{
		foreach ($this->timers as $name => $object)
		{
			if ($object->isSuspended())
				continue;

			if ($object->getTime() > time())
				continue;

			$this->bot->log('Triggering timer {name}', array('name' => $name), LogLevels::DEBUG);
			$oldtime = $object->getTime();

			if (!is_callable($object->getCall()))
			{
				$this->bot->log('Cleaning up timer {name} because it is no longer(?) callable.', array('name' => $name), LogLevels::DEBUG);
				$this->remove($name);
				continue;
			}

			call_user_func($object->getCall(), $object);

			// If the timer extended itself, keep it in the queue.
			if ($object->getTime() != $oldtime)
				continue;

			if ($object->getAutoCleanup())
			{
				$this->bot->log('Automatically cleaning up timer {name} because it was not extended and thus timed out.', array('name' => $name), LogLevels::DEBUG);
				$this->remove($name);
			}

			// If we're not allowed to automatically remove it, we'll just suspend it. Extending it will undo this.
			else
			{
				$this->bot->log('Suspending timer {name} because it is set to not be automatically removed but has timed out.', array('name' => $name), LogLevels::DEBUG);
				$object->suspend();
			}
		}
	}

	/**
	 * Get all timers for the specific time, allowing for fluctuation.
	 * @param int $time The time to get timers for.
	 * @param int $fluctuationPositive The fluctuation to allow in the positive range.
	 * @param int $fluctuationNegative The fluctuation to allow in the negative range.
	 * @return string[] The callable timers.
	 * @throws \InvalidArgumentException when any of the parameters are negative or not an int.
	 */
	public function find($time, $fluctuationPositive = 5, $fluctuationNegative = 5)
	{
		if (!is_int($time) || $time <= 0 || !is_int($fluctuationPositive) || !is_int($fluctuationNegative))
			throw new \InvalidArgumentException();

		$return = array();
		foreach ($this->timers as $name => $timer)
		{
			if ($timer->getTime() == $time || (($time - $fluctuationNegative <= $timer->getTime()) && ($timer->getTime() <= $time + $fluctuationPositive)))
				$return[] = $name;
		}

		return $return;
	}

	/**
	 * Gets a specific timer by name.
	 * @param string $name The timer name.
	 * @return Timer
	 * @throws \InvalidArgumentException when an invalid $name is passed.
	 * @throws TimerDoesNotExistException when the timer does not exist.
	 */
	public function get($name)
	{
		if (empty($name) || !is_string($name))
			throw new \InvalidArgumentException();

		if (!$this->exists($name))
			throw new TimerDoesNotExistException();

		return $this->timers[$name];
	}
}
