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
	public function register($event, $properties = array())
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
	 * @param string $hook  The hook to insert, as a function name.
	 *                      Pass as array($class, 'function') if in a class.
	 * @return bool Boolean determining if adding the hook succeeded.
	 */
	public function hook($event, $hook)
	{
		if (empty($event) || empty($hook))
			return false;

		// If we have not registered this event, say so.
		if (!$this->eventExists($event))
			trigger_error('The requested Event was not found: ' . $event . '. Your hook will be added but might not work until this Event becomes available.', E_USER_WARNING);

		// Does this event have the hook_once property set?
		if (!empty($this->getProperty($event, 'hook_once')))
		{
			// Already has hook(s)?
			if (!empty($this->eventDb[$event]))
				throw new \Exception(__CLASS__ . ': The Event ' . $event . ' has specified the hook_once property and a hook is already attached to it. No more hooks can be added');
		}

		// Already added this hook?
		if (in_array($hook, $this->eventDb[$event]))
		{
			trigger_error('A request to add a duplicate hook to event ' . $event . ' was ignored.', E_USER_WARNING);
			return false;
		}

		// Add it on the event train.
		$this->eventDb[$event][] = $hook;
		return true;
	}

	/**
	 * Remove a hook from an event.
	 * @param string $event The event to manipulate.
	 * @param string $hook  The hook to remove. Leave empty to remove all hooks.
	 * @return bool Boolean determining if the operation succeeded.
	 */
	public function unhook($event, $hook = '')
	{
		if (empty($event))
			return false;

		// Check if the event exists.
		if (!$this->eventExists($event))
			return false;

		if (!empty($hook))
		{
			// Does the hook exist?
			if (!in_array($hook, $this->eventDb[$event]))
				return;

			$key = array_search($hook, $event);
			unset($this->eventDb[$event][$key]);
		}
		else
			$this->eventDb[$event] = array();
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
	public function call($event, $data = null)
	{
		if (empty($event))
			return false;

		if (!$this->eventExists($event))
			throw new Exception('Call to undefined event ' . $event . ', please register events before calling them.', E_USER_WARNING);

		// Do we have any event hooks to call? If not, the call succeeded.
		if (empty($this->eventDb[$event]))
			return true;

		// So we have hooks. We might have data.
		if (!empty($data) && !is_array($data))
			$data = array($data);

		// Loop through each hook, see what we should do.
		foreach ($this->eventDb[$event] as $hook)
		{
			call_user_func($hook, $data);
		}
	}

	/**
	 * Gets the property of an event.
	 * @param string $event    The event to get the property from.
	 * @param string $property The property to get from the event.
	 * @return mixed The event data, or false upon nonexisting value/error.
	 */
	public function getProperty($event, $property)
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
