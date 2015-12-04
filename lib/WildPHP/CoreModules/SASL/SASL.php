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

namespace WildPHP\CoreModules;

use WildPHP\BaseModule;
use WildPHP\CoreModules\Connection\ConnectionModuleInterface;

class SASL extends BaseModule
{
	/**
	 * @var ConnectionModuleInterface
	 */
	protected $connection;

	public function setup()
	{
		$events = [
			'onConnect' => 'irc.connection.pre-created',
			'capListener' => 'irc.data.in.cap',
		];

		foreach ($events as $function => $event)
		{
			$this->getEventEmitter()->on($event, [$this, $function]);
		}
	}

	public function getConnectionModule()
	{
		if (empty($this->connection))
			$this->connection = $this->getModule('Connection');
	}

	public function onConnect()
	{
		$this->getConnectionModule();

		$generator = $this->connection->getGenerator();
		$this->connection->write('CAP REQ :sasl' . "\r\n");
	}

	public function capListener($message)
	{
		var_dump($message);
	}
}