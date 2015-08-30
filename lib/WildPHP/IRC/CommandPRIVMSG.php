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

class CommandPRIVMSG extends ServerMessage implements ICommandPRIVMSG
{
	protected $message;

	public function __construct(ServerMessage $ircMessage)
	{
		if (!($ircMessage instanceof ServerMessage))
			throw new \InvalidArgumentException('The provided argument is not an instance of ServerMessage.');

		if ($ircMessage->getCommand() != 'PRIVMSG')
			throw new \InvalidArgumentException('The provided message is not a PRIVMSG command.');

		$this->message = $ircMessage;
	}

	public function getHostname()
	{
		return $this->message->get()['prefix'];
	}

	public function getSender()
	{
		return new HostMask($this->getHostname());
	}

	public function getChannel()
	{
		return $this->message->get()['params']['receivers'];
	}

	public function getTargets()
	{
		return $this->getChannel();
	}

	public function getUserMessage()
	{
		return (string)$this->message->getParams()['text'];
	}

	public function getNickname()
	{
		return $this->message->get()['nick'];
	}
}
