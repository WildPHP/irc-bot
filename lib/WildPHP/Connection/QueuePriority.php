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
namespace WildPHP\Connection;

use MyCLabs\Enum\Enum;

/**
 * An enumeration class used for storing message/queue priorities.
 */
class QueuePriority extends Enum
{
	/**
	 * Messages in this queue are of critical priority and will be sent immediately.
	 * No limits are applied to this queue.
	 */
	const IMMEDIATE = 0;

	/**
	 * Messages in this queue are of high priority and will be sent as soon as possible.
	 */
	const HIGH = 1;

	/**
	 * Messages in this queue are neither important nor unimportant and will be sent normally.
	 */
	const NORMAL = 2;

	/**
	 * Messages in this queue are of low priority and may be postponed.
	 */
	const LOW = 3;

	/**
	 * Messages in this queue will not be present in standard queue output.
	 */
	const VOID = 4;
}
