<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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

	/**
	 * Task constructor.
	 *
	 * @param callable $callback
	 * @param int $time
	 * @param array $args
	 * @param int $repeatInterval
	 */
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

	public function cancel()
	{
		$this->setRepeatInterval(0);
		$this->setExpiryTime(time());
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