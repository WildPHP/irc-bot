<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Tasks;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ComponentTrait;

class TaskController
{
	use ComponentTrait;

	/**
	 * @var int
	 */
	protected $loopInterval = 2;

	/**
	 * @var Task[]
	 */
	protected $tasks = [];

	/**
	 * TaskController constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$container->getLoop()
			->addPeriodicTimer($this->loopInterval, [$this, 'runTasks']);
	}

	/**
	 * @param Task $task
	 *
	 * @return bool
	 */
	public function addTask(Task $task): bool
	{
		if ($this->taskExists($task))
			return false;

		$this->tasks[] = $task;

		return true;
	}

	/**
	 * @param Task $task
	 *
	 * @return bool
	 */
	public function removeTask(Task $task): bool
	{
		if (!$this->taskExists($task))
			return false;

		unset($this->tasks[array_search($task, $this->tasks)]);

		return true;
	}

	/**
	 * @param Task $task
	 *
	 * @return bool
	 */
	public function taskExists(Task $task): bool
	{
		return in_array($task, $this->tasks);
	}

	public function runTasks()
	{
		foreach ($this->tasks as $task)
		{
			if (time() < $task->getExpiryTime())
				continue;

			$args = array_merge([$task], $task->getStoredArguments());
			call_user_func_array($task->getCallback(), $args);

			$repeatInterval = $task->getRepeatInterval();
			if ($repeatInterval)
			{
				$task->setExpiryTime(time() + $repeatInterval);

				return;
			}

			$this->removeTask($task);
		}
	}
}