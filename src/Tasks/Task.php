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

namespace WildPHP\Core\Tasks;

class Task
{
	/**
	 * @var callable
	 */
	protected $callback = null;

	/**
	 * @var int
	 */
	protected $repeatInterval = 0;

	/**
	 * @var int
	 */
	protected $expiryTime = 0;

	/**
	 * @var array
	 */
	protected $storedArguments = [];

	public function __construct(callable $callback, int $time, array $args = [], int $repeatInterval = 0)
	{
		$this->setCallback($callback);
		$this->setExpiryTime($time);
		$this->setStoredArguments($args);
		$this->setRepeatInterval($repeatInterval);
	}

	/**
	 * @return callable
	 */
	public function getCallback(): callable
	{
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @return int
	 */
	public function getRepeatInterval(): int
	{
		return $this->repeatInterval;
	}

	/**
	 * @param int $repeatInterval
	 */
	public function setRepeatInterval(int $repeatInterval)
	{
		$this->repeatInterval = $repeatInterval;
	}

	/**
	 * @return int
	 */
	public function getExpiryTime(): int
	{
		return $this->expiryTime;
	}

	/**
	 * @param int $expiryTime
	 */
	public function setExpiryTime(int $expiryTime)
	{
		$this->expiryTime = $expiryTime;
	}

	/**
	 * @return array
	 */
	public function getStoredArguments(): array
	{
		return $this->storedArguments;
	}

	/**
	 * @param array $storedArguments
	 */
	public function setStoredArguments(array $storedArguments)
	{
		$this->storedArguments = $storedArguments;
	}
}