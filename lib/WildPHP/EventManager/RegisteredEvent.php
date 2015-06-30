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
namespace WildPHP\EventManager;

use \WildPHP\EventManager\ListenerPriority as Priority,
	WildPHP\Event\IEvent,
	\RuntimeException,
	\InvalidArgumentException;


/**
 * Represents a registered event within the event manager.
 */
class RegisteredEvent
{
	const EVENT_NAMESPACE = 'WildPHP\Event';

	/**
	 * Class name of the event.
	 * The event is guaranteed to be using this class name or its descendant when it is triggered.
	 */
	protected $className;

	/**
	 * An array of all listeners that are attached to this event.
	 */
	protected $listeners = array();

	/**
	 * Boolean telling if the listener array needs sorting.
	 */
	protected $isSorted = true;

	/**
	 * Creates a registered event with the specified class name.
	 * @param string $className the event's class name.
	 */
	public function __construct($className)
	{
		$this->className = (string) $className;
	}

	/**
	 * Registers a listener for this event at given priority.
	 * The listener function must be able to accept the event as its first parameter.
	 * Throws an exception when the listener is attempted to be registered for a second time.
	 * @param callable $listener The listener (as a callable function or method) that will be called when the event is triggered
	 * @param null|Priority $priority The priority this event will be ran with. Defaults to normal.
	 * @throws ListenerAlreadyRegisteredException
	 */
	public function registerListener(callable $listener, Priority $priority = null)
	{
		if($priority === null)
			$priority = new Priority(Priority::NORMAL);

		if($this->isListenerRegistered($listener))
			throw new ListenerAlreadyRegisteredException('Attempt to register event listener failed: listener already attached.');

		$this->isSorted = false;
		$this->listeners[$priority->getValue()][] = $listener;
	}

	/**
	 * Registers the final event handler.
	 * This is basically just a listener that is guaranteed to run last.
	 * @see registerListener($listener, Priority::HANDLER())
	 */
	public function registerEventHandler(callable $listener)
	{
		return $this->registerListener($listener, new Priority(Priority::HANDLER));
	}

	/**
	 * Checks whether a listener is already attached to this event.
	 * @param callable $listener The listener we are looking for.
	 * @return bool true when the listener is registered, false otherwise.
	 */
	public function isListenerRegistered(callable $listener)
	{
		foreach($this->listeners as $priority)
			if(in_array($listener, $priority, true))
				return true;

		return false;
	}

	/**
	 * Removes a listener from this event.
	 * @param callable $listener The listener we are removing.
	 * @throws ListenerNotRegisteredException if the listener is not registered.
	 */
	public function removeListener(callable $listener)
	{
		foreach($this->listeners as $level => $priority)
			if(($key = array_search($listener, $priority, true)) !== false)
			{
				unset($this->listeners[$level][$key]);
				return;
			}

		throw new ListenerNotRegisteredException('Attempt to remove event listener failed: this event did not register that listener.');
	}

	/**
	 * Sorts the listeners array (if necessary).
	 * @param bool $force Forces the sorting.
	 * @return bool True if the listeners were sorted, false otherwise.
	 */
	public function sortListeners($force = false)
	{
		if(!$this->isSorted || $force)
		{
			ksort($this->listeners, SORT_NUMERIC);
			$this->isSorted = true;
			return true;
		}

		return false;
	}

	/**
	 * Triggers the event, going through each listener according to their priority.
	 * Each listener gets executed and passed the event.
	 * Listeners with the same priority get executed in arbitrary order
	 * (usually from the first registered one to the last registered)
	 * 
	 * @param IEvent $event The event that gets passed to the listeners.
	 * @return int The number of listeners that were called.
	 */
	public function trigger(IEvent $event)
	{

		// make sure that the event we got is of the promised type (or a subclass)
		if(!is_a($event, self::EVENT_NAMESPACE . '\\' . $this->className))
			throw new InvalidEventTypeException('Cannot trigger event: Expected class ' . $this->className . ' or its subclass, got ' . get_class($event) . '.');

		// sort the listener array so that we actually run it in the correct order
		$this->sortListeners();

		$count = 0;
		foreach($this->listeners as $priority)
			foreach($priority as $listener)
			{
				call_user_func($listener, $event);
				$count++;
			}

		return $count;
	}

	/**
	 * Returns this event's class name.
	 * @return string the class name.
	 */
	public function getClassName()
	{
		return $this->className;
	}

}
