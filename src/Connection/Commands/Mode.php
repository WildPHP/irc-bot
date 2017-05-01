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

namespace WildPHP\Core\Connection\Commands;

class Mode extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $target;

	/**
	 * @var string
	 */
	protected $flags;

	/**
	 * @var string
	 */
	protected $args;

	/**
	 * @param string $target
	 * @param string $flags
	 * @param string $args
	 */
	public function __construct(string $target, string $flags, string $args)
	{
		$this->setTarget($target);
		$this->setFlags($flags);
		$this->setArgs($args);
	}

	/**
	 * @return string
	 */
	public function getTarget(): string
	{
		return $this->target;
	}

	/**
	 * @param string $target
	 */
	public function setTarget(string $target)
	{
		$this->target = $target;
	}

	/**
	 * @return string
	 */
	public function getFlags(): string
	{
		return $this->flags;
	}

	/**
	 * @param string $flags
	 */
	public function setFlags(string $flags)
	{
		$this->flags = $flags;
	}

	/**
	 * @return string
	 */
	public function getArgs(): string
	{
		return $this->args;
	}

	/**
	 * @param string $args
	 */
	public function setArgs(string $args)
	{
		$this->args = $args;
	}

	/**
	 * @return string
	 */
	public function formatMessage(): string
	{
		$target = $this->getTarget();
		$flags = $this->getFlags();
		$args = $this->getArgs();

		return 'MODE ' . $target . ' ' . $flags . ' ' . $args . "\r\n";
	}
}