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

use WildPHP\BaseModule;

class NewCommandEvent implements IEvent
{
	/**
	 * The command that was registered.
	 *
	 * @var string
	 */
	protected $command = null;

	/**
	 * The module that registered this.
	 *
	 * @var BaseModule|null
	 */
	protected $module = null;

	/**
	 * Construct method.
	 *
	 * @param string $command The command that was registered.
	 * @param BaseModule|null
	 */
	public function __construct($command, $module = null)
	{
		$this->setCommand($command);
		$this->setModule($module);
	}

	public function setCommand($command)
	{
		$this->command = $command;
	}

	public function getCommand()
	{
		return $this->command;
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
