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

class ChannelManager extends BaseModule
{
	/**
	 * List of channels the bot is currently in.
	 */
	private $channels = [];

	/**
	 * The Auth module's object.
	 *
	 * @var \WildPHP\Modules\Auth
	 */
	private $auth;

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Get the auth module.
		//$this->auth = $this->api->getModule('Auth');
	}

	/**
	 * Register commands.
	 */
	public function registerCommands()
	{
		return [
			'join' => [
				'callback' => 'joinCommand',
				'help'     => 'Joins a channel. Usage: join [channel] [channel] [...]',
				'auth'     => true
			],
			'part' => [
				'callback' => 'partCommand',
				'help'     => 'Leaves a channel. Usage: part [channel] [channel] [...]',
				'auth'     => true
			]
		];
	}

	/**
	 * Register listeners.
	 */
	public function registerListeners()
	{
		return [
			'initialJoin'            => 'irc.data.in.376',
			'channelMessageListener' => 'irc.data.in.privmsg',
			//'gateWatcher'            => 'irc.data.in',
			'channelMessageLogger'   => 'irc.data.'
		];
	}

	/**
	 * The Join command.
	 *
	 * @param array $data The last data received
	 */
	public function joinCommand($data)
	{
		if (empty($e->getParams()))
		{
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: join [#channel] [#channel] [...]'));
			return;
		}

		foreach ($e->getParams() as $chan)
		{
			if ($this->isInChannel($chan))
			{
				$this->log('Not joining channel {channel} because I am already part of it.', ['channel' => $chan], LogLevels::CHANNEL);
				continue;
			}

			$this->channels[] = $chan;
			$this->joinChannel($chan);
		}
	}

	/**
	 * The Part command.
	 *
	 * @param CommandEvent $e The last data received.
	 */
	public function partCommand(CommandEvent $e)
	{
		// If no argument specified, attempt to leave the current channel.
		if (empty($e->getParams()))
			$c = [$e->getMessage()->getChannel()];

		else
			$c = $e->getParams();

		$this->sendData(new PartCommand($c));
	}

	/**
	 * Join a channel.
	 *
	 * @param string|string[] $channel The channel name(s).
	 */
	public function joinChannel($channel)
	{
		if (!is_array($channel))
			$channel = [$channel];

		foreach ($channel as $id => $chan)
		{
			if (empty($chan) || !Validation::isChannel($chan))
				unset($channel[$id]);
		}

		$this->api->getIrcConnection()->write($this->api->getGenerator()->ircJoin(implode(',', $channel)));
	}

	/**
	 * This function handles the initial joining of channels.
	 *
	 * @param array $data
	 */
	public function initialJoin($data)
	{
		$channels = $this->api->getConfigurationStorage()->get('channels');

		foreach ($channels as $chan)
		{
			var_dump($chan);
			$this->joinChannel($chan);
		}
	}

	/**
	 * This function handles raising channelMessage events.
	 *
	 * @param array $data The last data received.
	 */
	public function channelMessageListener($data)
	{
		// A generic one and a specific one.
		$this->api->getEmitter()->emit('irc.message.channel', array($data['targets'][0], $data));
		$this->api->getEmitter()->emit('irc.message.channel.' . $data['targets'][0], array($data));
	}

	/**
	 * This function just logs data for channels.
	 *
	 * @param array $data The last data received.
	 */
	public function channelMessageLogger($data)
	{
		$message = $data['params']['text'];
		$nickname = $data['nick'];
		$channel = $data['targets'][0];

		if (substr($message, 0, 7) == chr(1) . 'ACTION')
			$message = '*' . trim(substr(substr($message, 7), 0, -1)) . '*';

		$this->api->getLogger()->info("({$channel}) <{$nickname}> {$message}");
	}

	/**
	 * This function watches for channel joins and parts, and keeps track of them.
	 *
	 * @param IRCMessageInboundEvent $e
	 */
	public function gateWatcher($e)
	{
		if (!empty($e->getMessage()->get()['code']) && $e->getMessage()->getCode() == 'RPL_TOPIC')
		{
			$channel = $e->getMessage()->getParams()[1];
			$this->log('Joined channel {channel}', ['channel' => $channel], LogLevels::CHANNEL);
			$this->addChannel($channel);
			$this->getEventManager()->getEvent('ChannelJoin')->trigger(new ChannelJoinEvent($channel));
		}

		if (($e->getMessage()->getCommand() == 'KICK' || $e->getMessage()->getCommand() == 'PART') && $e->getMessage()->getNickname() == $this->getNickname())
		{
			$channel = $e->getMessage()->getParams()['channel'];
			$this->log('Left channel {channel}', ['channel' => $channel], LogLevels::CHANNEL);
			$this->removeChannel($channel);
			$this->getEventManager()->getEvent('ChannelPart')->trigger(new ChannelPartEvent($channel));
		}
	}

	/**
	 * Adds a channel to the list.
	 *
	 * @param string $channel
	 */
	public function addChannel($channel)
	{
		if (!in_array($channel, $this->channels))
			$this->channels[] = $channel;
	}

	/**
	 * Removes a channel from the list.
	 *
	 * @param string $channel
	 */
	public function removeChannel($channel)
	{
		if (in_array($channel, $this->channels))
			unset($this->channels[array_search($channel, $this->channels)]);
	}

	/**
	 * Checks if the bot is in a channel.
	 *
	 * @param string $channel
	 *
	 * @return boolean
	 */
	public function isInChannel($channel)
	{
		return in_array($channel, $this->channels);
	}

	/**
	 * List all channels the bot is in
	 *
	 * @return string[]
	 */
	public function listChannels()
	{
		return $this->channels;
	}
}
