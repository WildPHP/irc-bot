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
namespace WildPHP\Event;

use \WildPHP\IRC\CommandPRIVMSG;

class CommandEvent implements ICommandEvent
{
	protected $command;
    protected $params;
	protected $message;

	public function __construct(CommandPRIVMSG $message)
	{
		$this->message = $message;
		$this->command = $message->getBotCommand();
		$this->params = $message->getBotCommandParams();
		
		if ($this->command === false)
			throw new \InvalidArgumentException('The given CommandPRIVMSG is not a bot command.');
	}
    
    public function getCommand()
    {
        return $this->command;
    }
    
    public function getParams()
    {
        return $this->params;
    }
	
	public function getMessage()
	{
		return $this->message;
	}
}
