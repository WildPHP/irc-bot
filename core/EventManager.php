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
namespace WildPHP\Core;

class EventManager
{
	// All available events. Used to determine if the event we're registering to is valid.
	// Store as 'event' => array('property' => 'value')
	private $available = array();

	// The event database, with the hooks attached.
	// Events are stored as 'event' => array('function', 'function')
	private $eventDb = array();

	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var object
	 */
	protected $bot;

	// Construct the class.
	public function __construct($bot)
	{
		// Set the bot.
		$this->bot = $bot;
	}

	/**
	 * Register a new event. Pass an array for multiple.
	 * @param string|array $event      The event name.
	 * @param array        $properties Any properties this event/these events should carry.
	 *                     Note: All properties in this array are set to all events to be registered.
	 * @return bool Boolean determining if registration of the event(s) succeeded.
	 */
	public function registerEvent($event, $properties = array())
	{
		if (empty($event))
			return false;

		if (!is_array($event))
			$event = array($event);

		if (!is_array($properties))
			throw new \Exception(__CLASS__ . ': The properties your event should carry must be an array.');

		foreach ($event as $e)
		{
			if (!$this->eventExists($e))
			{
				// And it's registered.
				$this->available[$e] = $properties;
				$this->eventDb[$e] = array();
				$this->bot->log('Event ' . $e . ' registered.', 'EVENTMGR');
			}
			else
				trigger_error('The following Event has already been registered: ' . $e . '. Ignoring duplicate register request.', E_USER_NOTICE);
		}

		return true;
	}

	/**
	 * Checks if an event exists.
	 * @param string $event The event to check.
	 * @return bool Boolean determining if the event exists.
	 */
	public function eventExists($event)
	{
		if (empty($event))
			return false;

		return array_key_exists($event, $this->available);
	}

	/**
	 * Hook into an event.
	 * @param string $event The event to hook into.
	 * @param mixed $hook The hook to insert, as a function name. Pass as array($class, 'function') if in a class.
	 * @return bool Boolean determining if adding the hook succeeded.
	 */
	public function registerEventListener($event, $listener, $priority = 3)
	{
		if (empty($event) || empty($listener))
			return false;

		// If we have not registered this event, say so.
		if (!$this->eventExists($event))
			trigger_error('The requested Event was not found: ' . $event . '. Your hook will be added but might not work until this Event becomes available.', E_USER_WARNING);

		// Does this event have the hook_once property set?
		if (!empty($this->getEventProperty($event, 'hook_once')) && !empty($this->eventDb[$event]))
				throw new \Exception(__CLASS__ . ': The Event ' . $event . ' has specified the hook_once property and a hook is already attached to it. No more hooks can be added');

		// Already added this hook?
		if (in_array($listener, $this->eventDb[$event]))
		{
			trigger_error('A request to add a duplicate hook to event ' . $event . ' was ignored.', E_USER_WARNING);
			return false;
		}

		// Priority codes. Such fun!
		// 5: Lowest: Called at the very latest moment, usually used for logging purposes.
		// 4: Low: Called later than normal. Used for non-important tasks.
		// 3: Medium/default: This should include the most hooks. Used for things that don't require real priority.
		// 2: High: Used for non-critical tasks that need to go before normal tasks.
		// 1: Highest: Used for absolute critical tasks. On error (return of false), the event is canceled.
		switch ($priority)
		{
			case 'highest':
			case 1:
				$priorityCode = 1;
				break;

			case 'high':
			case 2:
				$priorityCode = 2;
				break;

			default:
			case 'medium':
			case 3:
				$priorityCode = 3;
				break;

			case 'low':
			case 4:
				$priorityCode = 4;
				break;

			case 'lowest':
			case 5:
				$priorityCode = 5;
				break;
		}

		// Add it on the event train.
		$this->eventDb[$event][$priorityCode][] = $listener;
		return true;
	}

	/**
	 * Remove a listener from an event.
	 * @param string $event The event to manipulate.
	 * @param string $listener The hook to remove. Leave empty to remove all listeners
	 * @return bool Boolean determining if the operation succeeded.
	 */
	public function removeEventListener($event, $listener = '')
	{
		if (empty($event))
			return false;

		// Check if the event exists.
		if (!$this->eventExists($event))
			return false;

		if (!empty($listener))
		{
			foreach ($this->eventDb as $level => $hooks)
			{
				if (($key = array_search($listener, $hooks)) !== false)
				{
					unset($this->eventDb[$level][$key]);
					$this->bot->log('Removed listener ' . $listener . ' from event ' . $event, 'EVENTMGR');
					break;
				}
			}
		}
		else
			$this->eventDb[$event] = array();
	}

	/**
	 * Remove an event and its hooks.
	 * @param string $event The event to remove.
	 * @return bool Boolean determining if the operation succeeded.
	 */
	public function removeEvent($event)
	{
		if (empty($event))
			return false;

		// Does it exist?
		if (!$this->eventExists($event))
			return false;

		// Unset the event and its hooks.
		unset($this->eventDb[$event], $this->available[$event]);
		return true;
	}

	/**
	 * Returns available events with their hooks.
	 * @return array The events with their hooks.
	 */
	public function getEvents()
	{
		return $this->eventDb;
	}

	/**
	 * Calls an event.
	 * @param string $event The event to call.
	 * @param mixed  $data Data to send along with the event, to the hooks. Defaults to null.
	 * @return bool Boolean determining if the event call succeeded.
	 */
	public function triggerEvent($event, &$data = null)
	{
		if (empty($event))
			return false;

		if (!$this->eventExists($event))
			throw new \Exception('Call to undefined event ' . $event . ', please register events before calling them.', E_USER_WARNING);

		// Do we have any event hooks to call? If not, the call succeeded.
		if (empty($this->eventDb[$event]))
			return true;

		// call_user_func expects the parameters to pass as an array.
		if (!empty($data) && !is_array($data))
			$data = array($data);

		// Give the eventDb a good sort (so we get high priority first)
		ksort($this->eventDb[$event], SORT_NUMERIC);

		// Loop through each hook, see what we should do.
		foreach ($this->eventDb[$event] as $level => $hooks)
		{
			if (!$this->getEventProperty($event, 'surpress_log'))
				$this->bot->log('Calling ' . count($hooks) . ' listeners for event ' . $event . ' with priority level ' . $level . '...', 'EVENTMGR');

			foreach ($hooks as $hook)
			{
				$result = call_user_func($hook, $data);

				// If the event was cancelled.
				if ($level !== 5 && $result === false)
				{
					// Mark it as such.
					$data['event_cancelled'] = true;

					// Attempt to provide detailed data.
					$class = !empty($hook[0]) && is_object($hook[0]) ? get_class($hook[0]) : '(unrecognised module)';
					$hook = !empty($hook[1]) ? $hook[1] : '(anonymous/unrecognised function)';
					$this->bot->log('Event ' . $event . ' was marked as canceled by listener ' . $hook . ' called by module ' . $class, 'EVENTMGR');
				}
			}
		}
	}

	/**
	 * Gets the property of an event.
	 * @param string $event    The event to get the property from.
	 * @param string $property The property to get from the event.
	 * @return mixed The event data, or false upon nonexisting value/error.
	 */
	public function getEventProperty($event, $property)
	{
		if (empty($event) || empty($property))
			return false;

		// Check if the event exists.
		if (!$this->eventExists($event))
			return false;

		// No such property?
		if (!in_array($property, $this->available[$event]))
			return false;

		// Return the property.
		return $this->available[$event][$property];
	}
}
