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


class Pong extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $server1;

	/**
	 * @var string
	 */
	protected $server2 = '';

	/**
	 * @param string $server1
	 * @param string $server2
	 */
	public function __construct(string $server1, string $server2 = '')
	{
		$this->setServer1($server1);
		$this->setServer2($server2);
	}

	/**
	 * @return string
	 */
	public function getServer1(): string
	{
		return $this->server1;
	}

	/**
	 * @param string $server1
	 */
	public function setServer1(string $server1)
	{
		$this->server1 = $server1;
	}

	/**
	 * @return string
	 */
	public function getServer2(): string
	{
		return $this->server2;
	}

	/**
	 * @param string $server2
	 */
	public function setServer2(string $server2)
	{
		$this->server2 = $server2;
	}

	/**
	 * @return string
	 */
	public function formatMessage(): string
	{
		$server2 = $this->getServer2();
		return 'PONG ' . $this->getServer1() . (!empty($server2) ? ' ' . $server2 : '') . "\r\n";
	}
}