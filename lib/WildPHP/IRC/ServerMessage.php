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
namespace WildPHP\IRC;

use Phergie\Irc\Parser as PhergieParser;
use InvalidArgumentException;

class ServerMessage implements IServerMessage
{

	protected $message;

	/**
	 * Create a parsed IRC message from string.
	 * @param string $ircMessage The string to be parsed.
	 * @throws InvalidArgumentException
	 */
	public function __construct($ircMessage)
	{
		if (!is_string($ircMessage) || empty($ircMessage))
			throw new InvalidArgumentException('ircMessage is of invalid type or empty: expected string, got ' . gettype($ircMessage) . '.');

		$parser = new PhergieParser();
		$this->message = $parser->parse($ircMessage);
	}

	public function getMessage()
	{
		return $this->message['message'];
	}

	public function getCommand()
	{
		if (!empty($this->message['command']))
			return $this->message['command'];
		else
			return false;
	}

	public function getParams()
	{
		return (array) $this->message['params'];
	}

	public function getPrefix()
	{
		return (string) $this->message['prefix'];
	}

	public function getNickname()
	{
		return !empty($this->message['nick']) ? $this->message['nick'] : false;
	}

	public function getChannel()
	{
		return !empty($this->getParams()['channel']) ? $this->getParams()['channel'] : false;
	}

	public function getTargets()
	{
		return !empty($this->message['targets']) ? $this->message['targets'] : false;
	}

	public function getCode()
	{
		return !empty($this->message['code']) ? $this->message['code'] : false;
	}
	
	public function get()
	{
		return $this->message;
	}
}
