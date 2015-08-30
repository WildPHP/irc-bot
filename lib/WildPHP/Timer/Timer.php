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
namespace WildPHP\Timer;

class Timer // implements ITimer
{
	protected $triggerTime = 0;
	protected $call;
	protected $autoCleanup = true;
	protected $suspended = false;

	/**
	 * Sets up a timer.
	 *
	 * @param int      $time The timer object to add.
	 * @param callable $call The call to execute when the timer expires.
	 */
	public function __construct($time, $call)
	{
		if (!is_int($time) || !is_callable($call))
			throw new \InvalidArgumentException('Unable to add timer with invalid parameters. Expected int for time, got ' . gettype($time) . '; expected callable for call, got ' . gettype($call));

		if ($time <= 0)
			throw new \InvalidArgumentException('Cannot set a timer for a time less than or equal to 0.');

		$this->triggerTime = time() + $time;
		$this->call = $call;
	}

	/**
	 * Extends a timer.
	 *
	 * @param int $time The time to extend it with.
	 */
	public function extend($time)
	{
		if (!is_int($time) || $time <= 0)
			throw new \InvalidArgumentException();

		$this->triggerTime = $this->triggerTime + $time;
		$this->suspend(false);
	}

	/**
	 * (Re)sets the timer.
	 *
	 * @param int $time The time to set it.
	 */
	public function set($time)
	{
		if (!is_int($time) || $time <= 0)
			throw new \InvalidArgumentException();

		$this->triggerTime = time() + $time;
		$this->suspend(false);
	}

	/**
	 * Returns the time for this timer
	 *
	 * @return int
	 */
	public function getTime()
	{
		return $this->triggerTime;
	}

	/**
	 * Returns the callable for this timer.
	 *
	 * @return callable
	 */
	public function getCall()
	{
		return $this->call;
	}

	/**
	 * Enables or disables auto cleanup.
	 *
	 * @param bool $set
	 */
	public function autoCleanup($set = false)
	{
		$this->autoCleanup = (bool)$set;
	}

	/**
	 * Returns auto cleanup status.
	 *
	 * @return bool
	 */
	public function getAutoCleanup()
	{
		return $this->autoCleanup;
	}

	/**
	 * (Un)suspend this timer.
	 *
	 * @param bool $set
	 */
	public function suspend($set = true)
	{
		$this->suspended = (bool)$set;
	}

	/**
	 * Is the timer suspended?
	 *
	 * @return bool
	 */
	public function isSuspended()
	{
		return $this->suspended;
	}
}