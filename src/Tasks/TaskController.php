<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Tasks;

use WildPHP\Core\ComponentContainer;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class TaskController implements ComponentInterface
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