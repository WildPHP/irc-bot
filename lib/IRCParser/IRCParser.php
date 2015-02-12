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
	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var \WildPHP\Core\Bot
	 */
	private $bot;

	/**
	 * The command prefixes to use.
	 * @var array This array holds all command prefixes to match to.
	 */
	private $prefix;

	/**
	 * Set up the Parser.
	 * @param \WildPHP\Core\Bot $bot The Bot object.
	 */
	public function __construct(\WildPHP\Core\Bot $bot)
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

	/**
	 * Process a string and returns it in parsed format.
	 * @param string $message The string to parse.
	 * @return bool|array Boolean false on failure/malformed string, array with parsed data on success.
	 */
	public function process($message)
	{
		// No match? Bummer.
		if (!preg_match('/^(?::([^ ]+) )?([A-Z]+|\d{3}) ((?:(?! :)).*?)(?: :(.*))?$/', $message, $matches))
			return false;

		// We should have gathered everything we need.
		$data = array(
			'full' => $message,
			'hostname' => $matches[1],
			'command' => $matches[2],
			'arguments' => explode(' ', $matches[3]),
			'string' => !empty($matches[4]) ? $matches[4] : '',
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

	/**
	 * Parses a bot command and its arguments out of the specified $string.
	 * @param string $string The string to parse a bot command out of.
	 * @return array array containing the command and any arguments. Both values are string on success, or false/empty on failure.
	 */
	public function parseCommand($string)
	{
		$command_result = false;
		$string_result = false;

		// We have to do this for every prefix.
		foreach ($this->prefix as $prefix)
		{
			if (substr($string, 0, strlen($prefix)) != $prefix)
				continue;

			// Get the command.
			if (!preg_match('/' . preg_quote($prefix) . '([a-z0-9]+)/', $string, $botcmd))
				continue;

			// We detected a command! Good!
			$this->bot->log('Command detected: ' . $botcmd[1], 'COMMAND');
			$command_result = $botcmd[1];

			// Strip out the command to get what's left of it. That should be just the arguments.
			$string_result = trim(preg_replace('/' . preg_quote($prefix) . '([a-zA-Z0-9]+)/', '', $string));
			break;
		}

		// Return both.
		return array('bot_command' => $command_result, 'command_arguments' => $string_result);
	}
}
