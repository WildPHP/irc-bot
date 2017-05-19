<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

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

namespace WildPHP\Core\Connection;

use WildPHP\Core\Connection\IRCMessages\BaseMessage;

class QueueItem
{
	/**
	 * @var BaseMessage
	 */
	protected $commandObject;

	/**
	 * @var int
	 */
	protected $time;

	/**
	 * QueueItem constructor.
	 *
	 * @param BaseMessage $command
	 * @param int $time
	 */
	public function __construct(BaseMessage $command, int $time)
	{
		$this->setCommandObject($command);
		$this->setTime($time);
	}

	/**
	 * @return BaseMessage
	 */
	public function getCommandObject(): BaseMessage
	{
		return $this->commandObject;
	}

	/**
	 * @param BaseMessage $commandObject
	 */
	public function setCommandObject(BaseMessage $commandObject)
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