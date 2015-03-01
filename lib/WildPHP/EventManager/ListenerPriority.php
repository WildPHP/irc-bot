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

use MyCLabs\Enum\Enum;

/**
 * An enumeration class used for storing the event listener priorities.
 */
class ListenerPriority extends Enum
{
	/**
	 * Event listener is of low importance and should be ran first to allow others to influence the outcome.
	 */
	const LOW = 0;

	/**
	 * Event listener is neither important nor unimportant and may be ran normally.
	 */
	const NORMAL = 1;

	/**
	 * Event listener is of high importance and should be ran last to have the final say over the outcome.
	 */
	const HIGH = 2;

	/**
	 * Event listener monitors the final state of the event before it is passed to the handler.
	 * The event is not supposed to be changed once it reaches this category.
	 */
	const MONITOR = 3;

	/**
	 * The callback function is the event handler - it processes all the event's data and decides the outcome.
	 * (i.e. it runs the actual event-processing code)
	 */
	const HANDLER = 4;
}
