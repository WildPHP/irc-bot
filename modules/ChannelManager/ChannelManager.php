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

class ChannelManager
{
	static $dependencies = array('Auth');
	private $bot;
	private $channels;
	private $auth;
	public function __construct($bot)
	{
		$this->bot = $bot;

		// Register our command.
		$this->bot->registerEvent(array('command_join', 'command_part'), array('hook_once' => true));
		$this->bot->hookEvent('command_join', array($this, 'JoinCommand'));
		$this->bot->hookEvent('command_part', array($this, 'PartCommand'));

		// Get the auth module.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	public function JoinCommand($data)
	{
		if (empty($data['string']))
			return;

		if (!$this->auth->authUser($data['hostname']))
			return;

		// Join all specified channels.
		$c = explode(' ', $data['string']);

		foreach ($c as $chan)
		{
			$this->bot->log('Joining channel ' . $chan . '...', 'CHANMAN');
			$this->channels[] = $chan;
			$this->bot->sendData('JOIN ' . $chan);
		}
	}

	public function PartCommand($data)
	{
		if (!$this->auth->authUser($data['hostname']))
			return;
		
		// Part the current channel.
		if (empty($data['string']))
		{
			$chan = $data['argument'];

			$this->bot->log('Parting channel ' . $chan . '...', 'CHANMAN');
			$this->bot->sendData('PART ' . $chan);

			return;
		}

		// Part all specified channels.
		$c = explode(' ', $data['string']);

		foreach ($c as $chan)
		{
			$this->bot->log('Parting channel ' . $chan . '...', 'CHANMAN');
			$this->bot->sendData('PART ' . $chan);
		}
	}
}
