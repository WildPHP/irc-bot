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

class CommandPRIVMSG implements ICommandPRIVMSG
{
	protected $message;
	protected $prefix;

	public function __construct(ServerMessage $ircMessage, $commandPrefix)
	{
		if (!($ircMessage instanceof ServerMessage))
			throw new InvalidArgumentException('The provided argument is not an instance of ServerMessage.');
		
		if ($ircMessage->getCommand() != 'PRIVMSG')
			throw new InvalidArgumentException('The provided message is not a PRIVMSG command.');
		
		$this->message = $ircMessage;
		$this->prefix = (string) $commandPrefix;
	}

	public function getMessage()
	{
		return $this->message->getMessage();
	}

	public function getCommand()
	{
		return $this->message->getCommand();
	}

	public function getParams()
	{
		return $this->message->getParams();
	}

	public function getPrefix()
	{
		return $this->message->getPrefix();
	}
	
	public function getHostname()
	{
		return $this->message->get()['user'];
	}

	public function getSender()
	{
		return new HostMask($this->getHostname());
	}

	public function getTargets()
	{
		return $this->message->get()['params']['receivers'];
	}

	public function getUserMessage()
	{
		return (string) $this->message->getParams()['text'];
	}
	
	public function get()
	{
		return $this->message;
	}

    public function getBotCommand()
	{
		$pieces = explode(' ', $this->getUserMessage());
		
		if (substr($pieces[0], 0, strlen($this->prefix)) != $this->prefix)
			return false;
		
		return substr($pieces[0], strlen($this->prefix));
	}
	
	public function getBotCommandParams()
	{
		if ($this->getBotCommand() === false)
			return false;
		
		$pieces = explode(' ', $this->getUserMessage());
		array_shift($pieces);
		return $pieces;
	}
}
