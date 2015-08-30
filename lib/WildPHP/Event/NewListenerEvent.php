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
namespace WildPHP\Event;

use WildPHP\EventManager\RegisteredEvent;
use WildPHP\BaseModule;

class NewListenerEvent implements IEvent
{
	/**
	 * The event to work with.
	 *
	 * @var RegisteredEvent
	 */
	protected $event = null;

	/**
	 * The call to be made.
	 *
	 * @var callable
	 */
	protected $call = null;

	/**
	 * The module this listener originated from.
	 *
	 * @var BaseModule|null
	 */
	protected $module = null;

	/**
	 * Construct method.
	 *
	 * @param RegisteredEvent $event  The registeredEvent that brought us here.
	 * @param callable        $call   The call to be made.
	 * @param BaseModule|null $module The module that created the listener.
	 */
	public function __construct(RegisteredEvent $event, $call, $module = null)
	{
		echo 'Triggered NewListener...' . PHP_EOL;
		$this->setEvent($event);
		$this->setCall($call);
		$this->setModule($module);
	}

	public function setEvent(RegisteredEvent $event)
	{
		$this->event = $event;
	}

	public function getEvent()
	{
		return $this->event;
	}

	public function setCall($call)
	{
		$this->call = $call;
	}

	public function getCall()
	{
		return $this->call;
	}

	public function setModule($module)
	{
		$this->module = $module;
	}

	public function getModule()
	{
		return $this->module;
	}
}
