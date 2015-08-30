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
namespace WildPHP\Modules\Watchdog;

use WildPHP\IRC\IRCData;

class PongCommand extends IRCData
{
	/**
	 * The server to send to.
	 *
	 * @var string
	 */
	protected $server = '';

	/**
	 * @param string $server The server to send to.
	 */
	public function __construct($server)
	{
		$this->setServer($server);
	}

	/**
	 * Sets the server to send to.
	 *
	 * @param string $server
	 */
	public function setServer($server)
	{
		$this->server = $server;
	}

	/**
	 * Gets the server.
	 *
	 * @return string
	 */
	public function getServer()
	{
		return $this->server;
	}

	public function __toString()
	{
		return 'PONG ' . $this->getServer();
	}
}