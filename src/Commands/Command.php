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

namespace WildPHP\Core\Commands;


class Command
{
	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var CommandHelp
	 */
	protected $help = null;

	/**
	 * @var int
	 */
	protected $minimumArguments = -1;

	/**
	 * @var int
	 */
	protected $maximumArguments = -1;

	/**
	 * @var string
	 */
	protected $requiredPermission = '';

	/**
	 * @return callable
	 */
	public function getCallback(): callable
	{
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @return CommandHelp|null
	 */
	public function getHelp(): ?CommandHelp
	{
		return $this->help;
	}

	/**
	 * @param CommandHelp $help
	 */
	public function setHelp(CommandHelp $help)
	{
		$this->help = $help;
	}

	/**
	 * @return int
	 */
	public function getMinimumArguments(): int
	{
		return $this->minimumArguments;
	}

	/**
	 * @param int $minimumArguments
	 */
	public function setMinimumArguments(int $minimumArguments)
	{
		$this->minimumArguments = $minimumArguments;
	}

	/**
	 * @return int
	 */
	public function getMaximumArguments(): int
	{
		return $this->maximumArguments;
	}

	/**
	 * @param int $maximumArguments
	 */
	public function setMaximumArguments(int $maximumArguments)
	{
		$this->maximumArguments = $maximumArguments;
	}

	/**
	 * @return string
	 */
	public function getRequiredPermission(): string
	{
		return $this->requiredPermission;
	}

	/**
	 * @param string $requiredPermission
	 */
	public function setRequiredPermission(string $requiredPermission)
	{
		$this->requiredPermission = $requiredPermission;
	}


}