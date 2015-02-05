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
	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var \WildPHP\Core\Bot
	 */
	private $bot;

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
	 * The Event Manager object.
	 * @var \WildPHP\Core\EventManager
	 */
	private $evman;

	/**
	 * Set up the module.
	 * @param object $bot The Bot object.
	 */
	public function __construct(\WildPHP\Core\Bot $bot)
	{
		$this->bot = $bot;

		// Get the event manager over here.
		$this->evman = $this->bot->getEventManager();

		// Register our commands.
		$this->evman->registerEvent(array('command_join', 'command_part'), array('hook_once' => true));
		$this->evman->registerEventListener('command_join', array($this, 'JoinCommand'));
		$this->evman->registerEventListener('command_part', array($this, 'PartCommand'));

		// We also have a listener.
		$this->evman->registerEventListener('onDataReceive', array($this, 'initialJoin'));

		// Register any custom events.
		$this->evman->registerEvent('onInitialChannelJoin');

		// Get the auth module.
		$this->auth = $this->bot->getModuleInstance('Auth');

		// We're done, thanks!
		unset($bot);
	}

	/**
	 * Returns the module dependencies.
	 * @return array The array containing the module names of the dependencies.
	 */
	public static function getDependencies()
	{
		return array('Auth');
	}

	/**
	 * The Join command.
	 * @param array $data The last data received.
	 */
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

	/**
	 * The Part command.
	 * @param array $data The last data received.
	 */
	public function PartCommand($data)
	{
		if (!$this->auth->authUser($data['hostname']))
			return;

		// Part the current channel.
		if (empty($data['string']))
			$c = array($data['argument']);

		// Part all specified channels.
		else
			$c = explode(' ', $data['string']);

		foreach ($c as $chan)
		{
			$this->bot->log('Parting channel ' . $chan . '...', 'CHANMAN');
			$this->bot->sendData('PART ' . $chan);
		}
	}

	/**
	 * This function handles the initial joining of channels.
	 * @param array $data The last data received.
	 */
	public function initialJoin($data)
	{
		// Are we ready?
		$status = $data['command'] == '376' && $data['string'] == 'End of /MOTD command.';

		// Do any modules think we are ready?
		$this->evman->triggerEvent('onInitialChannelJoin', array(&$status));

		// And?
		if ($status)
		{
			$channels = $this->bot->getConfig('channels');

			foreach ($channels as $chan)
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
		if (!empty($channel))
			$this->bot->sendData('JOIN ' . $channel);
	}
}
