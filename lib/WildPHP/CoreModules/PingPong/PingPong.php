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

namespace WildPHP\CoreModules\PingPong;

use WildPHP\BaseModule;

class PingPong extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in.ping', [$this, 'respondPing']);
	}

	public function respondPing($data)
	{
		$connection = $this->getModulePool()->get('Connection');
		$connection->write($connection->getGenerator()->ircPong($data['params']['server1']));
	}
}