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
namespace IRCParser;
class IRCParser
{
	private $bot;

	// The command prefix.
	private $prefix;

	public function __construct($bot)
	{
		$this->bot = $bot;

		// Get the prefix.
		$this->prefix[] = $this->bot->getConfig('prefix');

		// Set up the bot name as a prefix.
		$nick = $this->bot->getConfig('nick');
		$this->prefix[] = $nick . ': ';
		$this->prefix[] = $nick . ', ';
		$this->prefix[] = $nick . ' ';


	}
	public function process($message)
	{
		// This array will contain everything the string includes.
		$data = array();

		// No match? Bummer.
		if (!preg_match('/^(?::([^ ]+) )?([A-Z]+|\d{3}) ((?:(?! :)).*?)(?: :(.*))?$/', $message, $matches))
			return false;

		// We should have gathered everything we need.
		$data = array(
			'full' => $message,
			'hostname' => $matches[1],
			'command' => $matches[2],
			'arguments' => explode(' ', $matches[3]),
			'string' => $matches[4],
		);

		// Does the hostname contain a nickname?
		if (preg_match('/([a-zA-Z0-9_]+)!/', $data['hostname'], $username))
			$data['nickname'] = $username[1];

		// Time for command parsing.
		if ($data['command'] == 'PRIVMSG')
		{
			// Get any possible commands.
			$result = $this->parseCommand($data['string']);

			// Got one?
			if (!empty($result['bot_command']))
				$data = array_merge($data, $result);
		}

		return $data;
	}

	public function parseCommand($string)
	{
		$command = false;
		$string = false;

		// We have to do this for every prefix.
		foreach ($this->prefix as $prefix)
		{
			if (substr($string, 0, strlen($prefix)) != $prefix)
				continue;

			// Get the command.
			if (!preg_match('/' . preg_quote($prefix) . '([a-z0-9]+)/', strtolower($string), $botcmd))
				continue;

			// We detected a command! Good!
			$this->bot->log('Command detected: ' . $botcmd[1], 'COMMAND');
			$command = $botcmd[1];

			// Strip out the command to get what's left of it. That should be just the arguments.
			$string = trim(preg_replace('/' . preg_quote($prefix) . '([a-zA-Z0-9]+)/', '', $string));
			break;
		}

		// Return both.
		return array('bot_command' => $command, 'command_arguments' => $string);
	}
}
