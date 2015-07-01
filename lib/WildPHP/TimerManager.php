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

use WildPHP\Manager;
use WildPHP\Timer\Timer;

class TimerManager extends Manager
{
	/**
	 * The timers. Stored as array<timestamp, callable>
	 * @var array<string, Timer>
	 */
	protected $timers = array();
	
	/**
	 * Adds a timer.
	 * @param string $name The name to set for the timer.
	 * @param Timer $timer The timer object to add.
	 */
	public function add($name, $timer)
	{
		if (!is_object($timer) || !($timer instanceof Timer) || empty($name))
			throw new \InvalidArgumentException('Unable to add timer with invalid parameters.');
		
		if ($this->exists($name))
			throw new TimerExistsException('The specified timer already exists.');
		
		$this->bot->log('Added new timer ' . $name . '. Next trigger in ' . ($timer->getTime() - time()) . ' seconds.', 'TIMERMAN');		
		$this->timers[$name] = $timer;
	}
	
	/**
	 * Checks if a timer exists, searching on the name.
	 * @param string $name
	 */
	public function exists($name)
	{
		return array_key_exists($name, $this->timers);
	}
	
	/**
	 * Removes a timer.
	 * @param callable $name The call to remove.
	 */
	public function remove($name)
	{
		if (!$this->exists($name))
			throw new TimerDoesNotExistException();
		
		unset($this->timers[$name]);
		$this->bot->log('Removed timer ' . $name, 'TIMERMAN');
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
			
			if ($object->getTime() <= time())
			{
				$this->bot->log('Triggering timer ' . $name, 'TIMERMAN');
				$oldtime = $object->getTime();
				
				if (!is_callable($object->getCall()))
				{
					$this->bot->log('Cleaning up timer ' . $name . ' because it is no longer(?) callable.', 'TIMERMAN');
					$this->remove($name);
					continue;
				}
				
				call_user_func($object->getCall(), $object);
				
				if ($object->getTime() == $oldtime)
				{
					if ($object->getAutoCleanup())
					{
						$this->bot->log('Automatically cleaning up timer ' . $name . ' because it was not extended and thus timed out.', 'TIMERMAN');
						$this->remove($name);
					}
					
					// If we're not allowed to automatically remove it, we'll just suspend it. Extending it will undo this.
					else
					{
						$this->bot->log('Suspending timer ' . $name . ' because it should not be automatically removed but has timed out.', 'TIMERMAN');
						$object->suspend();
					}
				}
			}
		}
	}
	
	/**
	 * Get all timers for the specific time, allowing for fluctuation.
	 * @param int $time The time to get timers for.
	 * @param int $fluctuationPositive The fluctuation to allow in the positive range.
	 * @param int $fluctuationNegative The fluctuation to allow in the negative range.
	 * @return string[] The callable timers.
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

class TimerExistsException extends \RuntimeException
{
}
class TimerDoesNotExistException extends \RuntimeException
{
}