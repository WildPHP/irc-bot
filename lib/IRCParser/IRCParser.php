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
		$this->prefix[] = $this->bot->getConfiguration('prefix');

		// Set up the bot name as a prefix.
		$nick = $this->bot->getConfiguration('nick');
		$this->prefix[] = $nick . ': ';
		$this->prefix[] = $nick . ', ';


	}
	public function process($message)
	{
		// Commands not automagically detected.
		$commands = array('PING');

		// This array will contain everything the string includes.
		$data = array();

		// Try to filter the hostname.
		if (preg_match('/:([a-zA-Z0-9_.!~@\/-]+)/', $message, $hostname))
		{
			$data['hostname'] = $hostname[1];

			// Can we also parse a username out of it?
			if (preg_match('/:([a-zA-Z0-9_]+)!/', $message, $username))
				$data['from'] = $username[1];
		}

		// Parse the command. And optional parameters.
		if (preg_match('/ ?([A-Z0-9]+) ([^ ]+)?/', $message, $command))
		{
			$data['command'] = $command[1];

			if (!empty($command[2]))
				$data['argument'] = $command[2];
		}
		else
		{
			$data['command'] = '';
			foreach ($commands as $command)
			{
				if (substr($message, strlen($command)) == $command)
					$data['command'] = $command;
			}
		}

		// Strip both.
		$data['string'] = trim(preg_replace('/(:[a-zA-Z0-9.!~@\/]+) ([A-Z0-9]+) ([^ ]+)?/', '', $message));

		// Strip off the ':' we sometimes get.
		if (substr($data['string'], 0, 1) == ':')
			$data['string'] = substr($data['string'], 1, strlen($data['string']));

		// Try to filter the command out, if it's in there.
		foreach ($this->prefix as $prefix)
		{
			if (substr($data['string'], 0, strlen($prefix)) == $prefix)
			{
				// Get the command.
				if (preg_match('/' . preg_quote($prefix) . '([a-zA-Z0-9]+)/', $data['string'], $botcmd))
				{
					$this->bot->log('Command detected: ' . $botcmd[1], 'COMMAND');
					$data['bot_command'] = $botcmd[1];
				}
			}
		}

		return $data;
	}
}
