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
	private $available = array();

	// The event database.
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
		// Register some default events.
		$this->register(array('onConnect', 'onDataReceive', 'onDataSend',
			'onSay'));

		// Set the bot.
		$this->bot = $bot;
	}

	/**
	 * Register a new event. Pass an array for multiple.
	 * @param string|array $event The event name.
	 * @return bool Boolean determining if registration of the event(s) succeeded.
	 */
	public function register($event, $trigger = null)
	{
		if (empty($event))
			return false;

		if (!is_array($event))
			$event = array($event);

		foreach ($event as $e)
		{
			if (!$this->eventExists($e))
			{
				// And it's registered.
				$this->available[] = $e;
				$this->eventDb[$e] = array();
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

		return in_array($event, $this->available);
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
			if (!empty($data))
				call_user_func_array($hook, $data);
			else
				call_user_func($hook);
		}
	}
}
