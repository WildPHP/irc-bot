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

use WildPHP\Manager;
use WildPHP\Bot;
use WildPHP\EventManager\RegisteredEvent;
use RuntimeException;
use InvalidArgumentException;


/**
 * The bot's event manager.
 * It manages data processing, makes sure methods that are supposed to be ran
 * on certain actions are indeed handled and ran properly.
 *
 * Note that all event names are validated and by default are expected
 * to be conform with the EVENT_NAME_PATTERN regex.
 */
class EventManager extends Manager
{

	const EVENT_NAME_PATTERN = '/^[a-z]+$/i';

	/**
	 * Holds registered events, their hooks and other data.
	 * Each event is stored as array()
	 */
	private $events = array();

	/**
	 * Validates a name using EVENT_NAME_PATTERN, throwing an exception when the name is invalid.
	 * @param string $name The name that will be checked
	 * @param string $message Optional message that is thrown with the exception.
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public static function assertValidName($name, $message = 'Invalid name.')
	{
		if(!is_string($name) || !preg_match(self::EVENT_NAME_PATTERN, $name))
			throw new InvalidArgumentException($message . ' Name was "' . $name . '".');
	}

	/**
	 * Registers a new event with this manager under a specific event name.
	 * If you attempt to register already registered event nothing happens unless
	 * the classes mismatch. In that case an exception is thrown.
	 * @param string  $eventName Name of the event that is being registered.
	 * @param RegisteredEvent $registeredEvent The class instance to use for this event.
	 * @throws EventAlreadyRegisteredException on critical failure
	 * @return bool True on success, false on failure.
	 */
	public function register($eventName, RegisteredEvent $registeredEvent)
	{
		self::assertValidName($registeredEvent->getClassName(), 'Event registration failed: Invalid event class name.');

		// check if it is registered first - it also validates the name, no need to do that twice
		if($this->isRegistered($eventName))
		{
			// check whether the event we are registering is the same class (or subclass) of what we have already registered
			if(is_a($registeredEvent->getClassName(), $this->events[$eventName]->getClassName(), true))
				$this->log('Event ' . $eventName . ' has been previously registered, skipping request.');
			else
				throw new EventAlreadyRegisteredException('Event registration failed: Event ' . $eventName . ' has been previously registered with a different class name (' . $this->events[$eventName]->getClassName() . ').');
			return false;
		}

		$this->events[$eventName] = $registeredEvent;
		$this->log('Registered event ' . $eventName . ' (with class ' . $registeredEvent->getClassName() . ').');
		return true;
	}

	/**
	 * Checks whether an event is registered with this manager.
	 * @param string $eventName The event to check.
	 * @return bool true if event exists, false otherwise.
	 */
	public function isRegistered($eventName)
	{
		self::assertValidName($eventName, 'Invalid event name.');

		return array_key_exists($eventName, $this->events);
	}

	/**
	 * Removes a registered event from this manager.
	 * @param string $eventName The event to remove.
	 * @return boolean|null Boolean determining if the operation succeeded.
	 */
	public function remove($eventName)
	{
		// check if it is registered first - it also validates the name, no need to do that twice
		if(!$this->isRegistered($eventName))
			throw new EventDoesNotExistException('Could not remove registered event: Event ' . $eventName . ' is not registered.');

		unset($this->events[$eventName]);
	}

	/**
	 * Returns a registered event allowing you to manipulate it.
	 * @param string $eventName The event to get.
	 * @return RegisteredEvent The events with their hooks.
	 */
	public function getEvent($eventName)
	{
		// check if it is registered first - it also validates the name, no need to do that twice
		if(!$this->isRegistered($eventName))
			throw new EventDoesNotExistException('Event ' . $eventName . ' is not registered.');

		return $this->events[$eventName];
	}
}
