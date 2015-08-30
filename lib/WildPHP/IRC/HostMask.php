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
namespace WildPHP\IRC;

/**
 * This class holds data about a specific host mask.
 */
class HostMask
{
	/**
	 * The hostmask.
	 *
	 * @var string
	 */
	protected $hostmask;

	/**
	 * The class constructor.
	 *
	 * @param string $hostmask The hostmask  to manipulate.
	 */
	public function __construct($hostmask)
	{
		$this->hostmask = $hostmask;
	}

	/**
	 * Getter for $hostmask.
	 *
	 * @return string
	 */
	public function getHostMask()
	{
		return $this->hostmask;
	}

	/**
	 * Converts the hostmask to a string.
	 */
	public function __toString()
	{
		return $this->getHostMask();
	}
}
