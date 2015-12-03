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

namespace WildPHP\CoreModules\Commands;

use WildPHP\BaseModule;

class Commands extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in.privmsg', [$this, 'parseCommands']);

		$this->getEventEmitter()->on('irc.command.ping', function ($command, $params, $data)
		{
			$connection = $this->getModule('Connection');
			$connection->write($connection->getGenerator()->ircPrivmsg($data['targets'][0], 'Pong!'));
		});
	}

	public function parseCommands($data)
	{
		$configuration = $this->getModulePool()->get('Configuration');

		$command = '([a-zA-Z0-9]+)';
		$params = '(?: (.+))?';
		$tests = [
			$configuration->get('nick') . "[^a-zA-Z0-9]+{$command}{$params}",
			preg_quote($configuration->get('prefix')) . "{$command}{$params}"
		];

		$command = '';
		$params = '';
		foreach ($tests as $test)
		{
			if (preg_match('/^' . $test . '/', $data['params']['text'], $out) === false || empty($out))
				continue;

			$command = strtolower($out[1]);

			// Done like this as to not cause an exception.
			$params = array_key_exists(2, $out) ? $out[2] : '';
			break;
		}

		if (empty($command))
			return;

		$this->getEventEmitter()->emit('irc.command.' . $command, [$command, $params, $data]);
	}
}