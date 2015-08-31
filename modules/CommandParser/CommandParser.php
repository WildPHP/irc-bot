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

namespace WildPHP\Modules;

use WildPHP\BaseModule;

class CommandParser extends BaseModule
{
	// Register our events.
	public function registerListeners()
	{
		return [
			'parseCommand' => 'irc.data.in.privmsg'
		];
	}

	public function parseCommand($data)
	{
		// Parse some stuffs. Test against all these criteria:
		$command = '([a-zA-Z0-9]+)';
		$params = '(?: (.+))?';
		$tests = [
			//$this->getNickname() . "[,: ] {$command}{$params}",
			preg_quote($this->api->getConfigurationStorage()->get('prefix')) . "{$command}{$params}"
		];

		$command = '';
		$params = '';
		foreach ($tests as $test)
		{
			if (preg_match('/' . $test . '/', $data['params']['text'], $out) === false || empty($out))
				continue;

			$command = strtolower($out[1]);

			// Done like this as to not cause an exception.
			$params = array_key_exists(2, $out) ? $out[2] : '';
			break;
		}

		if (empty($command))
			return;

		$this->api->getEmitter()->emit('irc.command.' . $command, array($command, $params, $data));
	}
}