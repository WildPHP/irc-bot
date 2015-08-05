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
namespace WildPHP\Modules\ChannelManager\Event;

use WildPHP\IRC\CommandPRIVMSG;

class ChannelMessageEvent implements IChannelEvent
{
	/**
	 * The channel.
	 * @var string
	 */
	protected $channel;

	/**
	 * The message.
	 * @var CommandPRIVMSG
	 */
	protected $message;

	/**
	 * Constructs the event.
	 * @param string $channel The channel that is being joined.
	 * @param CommandPRIVMSG $message The message that is received.
	 */
	public function __construct($channel, CommandPRIVMSG $message)
	{
		$this->channel = $channel;
		$this->message = $message;
	}

	public function getChannel()
	{
		return $this->channel;
	}

	public function getMessage()
	{
		return $this->message;
	}
}
