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
namespace WildPHP\Modules\CommandParser\Event;

use WildPHP\Event\ICommandEvent;
use \WildPHP\IRC\CommandPRIVMSG;

/**
 * Class CommandEvent
 *
 * @package WildPHP\Modules\CommandParser\Event
 */
class CommandEvent implements ICommandEvent
{
	/**
	 * Command name.
	 *
	 * @var string
	 */
	protected $command;

	/**
	 * Parameters received.
	 *
	 * @var string[]
	 */
	protected $params;

	/**
	 * The message received.
	 *
	 * @var CommandPRIVMSG
	 */
	protected $message;

	/**
	 * @param CommandPRIVMSG $message
	 * @param string         $command
	 * @param string         $params
	 */
	public function __construct(CommandPRIVMSG $message, $command, $params)
	{
		$this->message = $message;

		if (empty($command) || $params === null)
			throw new \InvalidArgumentException('This CommandPRIVMSG does not have a command associated; CommandEvent can not be fired.');

		$this->command = $command;
		$this->params = empty($params) ? [] : explode(' ', $params);
	}

	/**
	 * @return string
	 */
	public function getCommand()
	{
		return strtolower($this->command);
	}

	/**
	 * @return array|\string[]
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @return CommandPRIVMSG
	 */
	public function getMessage()
	{
		return $this->message;
	}
}
