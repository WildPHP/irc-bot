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

namespace WildPHP;

abstract class BaseModule
{
	/**
	 * The module directory.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * The Api.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Set up the module.
	 *
	 * @param Api $api The current Api.
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
		$dirname = explode('\\', get_class($this));
		$this->dir = WPHP_MODULE_DIR . '/' . end($dirname) . '/';
	}

	/**
	 * Init the module.
	 */
	public function init()
	{
		if (method_exists($this, 'setup'))
		{
			$result = $this->setup();

			if ($result === false)
			{
				$this->api->getLogger()->debug('Module initialisation canceled. The module will remain loaded.');
				return;
			}
		}

		// Set up predefined events, then.
		$this->handleListeners();
	}

	/**
	 * Return registered listeners.
	 *
	 * @return array
	 */
	public function getListeners()
	{
		if (!method_exists($this, 'registerListeners'))
			return [];

		return $this->registerListeners();
	}

	/**
	 * Handle event registering.
	 */
	private function handleListeners()
	{
		$events = [];
		if (method_exists($this, 'registerListeners'))
			$events = $this->registerListeners();

		// Attempt to add command events into the same array.
		if (method_exists($this, 'registerCommands'))
		{
			$commands = $this->registerCommands();

			foreach ($commands as $command => $params)
			{
				$event = 'irc.command.' . $command;
				$callback = $params['callback'];

				$events[$callback] = $event;
			}
		}

		if (!is_array($events))
			return;

		$this->registerEvents($events);
	}

	/**
	 * Registers multiple events in an array.
	 *
	 * @param array $events The events to register, in the 'callback' => 'event' format.
	 */
	public function registerEvents(array $events)
	{
		if (empty($events))
			return;

		foreach ($events as $callback => $event)
		{
			if (!is_callable([$this, $callback]))
				return;

			$this->api->getEmitter()->on($event, [$this, $callback]);
		}
	}

	/**
	 * Return the working directory of this module.
	 *
	 * @return string
	 */
	public function getWorkingDir()
	{
		return $this->dir;
	}
}