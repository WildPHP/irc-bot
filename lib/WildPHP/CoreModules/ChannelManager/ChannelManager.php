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

namespace WildPHP\CoreModules\ChannelManager;

use WildPHP\BaseModule;
use WildPHP\CoreModules\Connection\Connection;
use WildPHP\Validation;

class ChannelManager extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in.376', [$this, 'initialJoin']);
	}

	public function initialJoin($data)
	{
		$configuration = $this->getModulePool()->get('Configuration');
		$logger = $this->getModulePool()->get('Logger');
		$channels = $configuration->get('channels');

		if (empty($channels))
			return;

		foreach ($channels as $chan)
		{
			if (!Validation::isChannel($chan))
				continue;

			$logger->info('Auto-joining channel ' . $chan . '...');
			$this->joinChannel($chan);
		}
	}

	/**
	 * @param string $channel
	 */
	public function joinChannel($channel)
	{
		if (!Validation::isChannel($channel))
			return;

		$connection = $this->getModulePool()->get('Connection');

		if (!($connection instanceof Connection))
			return;

		$connection->write($connection->getGenerator()->ircJoin($channel));
	}
}