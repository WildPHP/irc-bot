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

class ChannelManager extends BaseModule
{
	/**
	 * List of channels the bot is currently in.
	 */
	private $channels = array();

	/**
	 * The Auth module's object.
	 * @var \WildPHP\Modules\Auth
	 */
	private $auth;
	
    /**
     * Dependencies of this module.
     * @var string[]
     */
    protected static $dependencies = array('Auth');

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our commands.
		$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'joinCommand'));
		$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'partCommand'));

		// We also have a listener.
		//$this->evman->getEvent('IRCMessageInbound')->registerListener(array($this, 'initialJoin'));

		// Get the auth module.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * The Join command.
	 * @param array $e The last data received.
	 */
	public function joinCommand($e)
	{
		if ($e->getCommand() != 'join' || empty($e->getParams()) || !$this->auth->authUser($e->getMessage()->getSender()))
			return;

		foreach($e->getParams() as $chan)
		{
			$this->bot->log('Joining channel ' . $chan . '...', 'CHANMAN');
			$this->channels[] = $chan;
			$this->bot->sendData('JOIN ' . $chan);
		}
	}

	/**
	 * The Part command.
	 * @param array $e The last data received.
	 */
	public function partCommand($e)
	{
		if ($e->getCommand() != 'part' || !$this->auth->authUser($e->getMessage()->getSender()))
			return;
		
		// If no argument specified, attempt to leave the current channel.
		if (empty($e->getParams()))
			$c = array($e->getMessage()->getTargets());
			
		else
			$c = $e->getParams();

		foreach($c as $chan)
		{
			$this->bot->log('Parting channel ' . $chan . '...', 'CHANMAN');
			$this->bot->sendData('PART ' . $chan);
		}
	}

	/**
	 * This function handles the initial joining of channels.
	 * @param array $e The last data received.
	 */
	public function initialJoin($e)
	{
		// Are we ready?
		$status = $e['command'] == '376' && $e['string'] == 'End of /MOTD command.';

		// Do any modules think we are ready?
		$this->evman->triggerEvent('onInitialChannelJoin', array(&$status));

		// And?
		if($status)
		{
			$channels = $this->bot->getConfig('channels');

			foreach($channels as $chan)
			{
				$this->joinChannel($chan);
			}

			$this->evman->removeEventListener('onDataReceive', array($this, 'initialJoin'));
		}
	}

	/**
	 * Join a channel.
	 * @param string $channel The channel name.
	 */
	public function joinChannel($channel)
	{
		if(!empty($channel))
			$this->bot->sendData('JOIN ' . $channel);
	}
}
