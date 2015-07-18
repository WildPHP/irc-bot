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
use WildPHP\Validation;
use WildPHP\Event\CommandEvent;
use WildPHP\EventManager\RegisteredEvent;
use WildPHP\Event\ChannelJoinEvent;
use WildPHP\Event\ChannelPartEvent;

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
		$botCommand = $this->evman()->getEvent('BotCommand');
		$botCommand->registerCommand('join', array($this, 'joinCommand'), true);
		$botCommand->registerCommand('part', array($this, 'partCommand'), true);
		
		// Register a new event.
		$channelJoin = new RegisteredEvent('ChannelJoinEvent');
		$this->evman()->register('ChannelJoin', $channelJoin);

		// We also have a listener.
		$this->evman()->getEvent('IRCMessageInbound')->registerListener(array($this, 'initialJoin'));

		// Get the auth module.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * The Join command.
	 * @param CommandEvent $e The last data received.
	 */
	public function joinCommand($e)
	{
		if (empty($e->getParams()))
		{
			$this->bot->say('Not enough parameters. Usage: join [#channel] [#channel] [...]');
			return;
		}

		foreach($e->getParams() as $chan)
		{
			$this->bot->log('Joining channel ' . $chan . '...', 'CHANMAN');
			$this->channels[] = $chan;
			$this->bot->sendData('JOIN ' . $chan);
		}
	}

	/**
	 * The Part command.
	 * @param CommandEvent $e The last data received.
	 */
	public function partCommand($e)
	{
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
	 * @param IRCMessageInboundEvent $e The last data received.
	 */
	public function initialJoin($e)
	{
		// Are we ready?
		$status = $e->getMessage()->getCommand() == '376' && $e->getMessage()->get()['code'] == 'RPL_ENDOFMOTD';

		// And?
		if ($status)
		{
			$channels = $this->bot->getConfig('channels');

			foreach($channels as $chan)
			{
				$this->joinChannel($chan);
			}

			$this->evman()->getEvent('IRCMessageInbound')->removeListener(array($this, 'initialJoin'));
		}
	}

	/**
	 * Join a channel.
	 * @param string $channel The channel name.
	 */
	public function joinChannel($channel)
	{
		if(!empty($channel) && Validation::isChannel($channel))
		{
			$this->evman()->getEvent('ChannelJoin')->trigger(new ChannelJoinEvent($channel));
			$this->bot->sendData('JOIN ' . $channel);
		}
	}
}
