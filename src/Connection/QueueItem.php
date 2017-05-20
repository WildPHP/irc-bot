<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Core\Connection\IRCMessages\SendableMessage;

class QueueItem
{
	/**
	 * @var SendableMessage
	 */
	protected $commandObject;

	/**
	 * @var int
	 */
	protected $time;

	/**
	 * QueueItem constructor.
	 *
	 * @param SendableMessage $command
	 * @param int $time
	 */
	public function __construct(SendableMessage $command, int $time)
	{
		$this->setCommandObject($command);
		$this->setTime($time);
	}

	/**
	 * @return SendableMessage
	 */
	public function getCommandObject(): SendableMessage
	{
		return $this->commandObject;
	}

	/**
	 * @param SendableMessage $commandObject
	 */
	public function setCommandObject(SendableMessage $commandObject)
	{
		$this->commandObject = $commandObject;
	}

	/**
	 * @return int
	 */
	public function getTime(): int
	{
		return $this->time;
	}

	/**
	 * @param int $time
	 */
	public function setTime(int $time)
	{
		$this->time = $time;
	}

	/**
	 * @return bool
	 */
	public function itemShouldBeTriggered(): bool
	{
		return time() >= $this->getTime();
	}
}