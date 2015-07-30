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
use WildPHP\IRC\CommandPRIVMSG;
use WildPHP\LogManager\LogLevels;
use WildPHP\Modules\ChannelManager\Event\ChannelMessageEvent;
use WildPHP\Validation;
use WildPHP\Event\CommandEvent;
use WildPHP\Event\IRCMessageInboundEvent;
use WildPHP\EventManager\RegisteredModuleEvent;
use WildPHP\Modules\ChannelManager\Event\ChannelJoinEvent;
use WildPHP\Modules\ChannelManager\Event\ChannelPartEvent;

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
	protected static $dependencies = array('Auth', 'Help');

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our commands.
		$botCommand = $this->evman()->getEvent('BotCommand');
		$botCommand->registerCommand('join', array($this, 'joinCommand'), true);
		$botCommand->registerCommand('part', array($this, 'partCommand'), true);

		$helpmodule = $this->bot->getModuleInstance('Help');
		$helpmodule->registerHelp('join', 'Joins a channel. Usage: join [channel] [channel] [...]');
		$helpmodule->registerHelp('part', 'Leaves a channel. Usage: part [channel] [channel] [...]');

		// Register a new event.
		$channelJoin = new RegisteredModuleEvent('WildPHP\\Modules\\ChannelManager\\Event\\ChannelJoinEvent');
		$this->evman()->register('ChannelJoin', $channelJoin);

		$channelPart = new RegisteredModuleEvent('WildPHP\\Modules\\ChannelManager\\Event\\ChannelPartEvent');
		$this->evman()->register('ChannelPart', $channelPart);

		$channelMessage = new RegisteredModuleEvent('WildPHP\\Modules\\ChannelManager\\Event\\ChannelMessageEvent');
		$this->evman()->register('ChannelMessage', $channelMessage);

		// We also have a listener. Or more.
		$this->evman()->getEvent('IRCMessageInbound')->registerListener(array($this, 'initialJoin'));
		$this->evman()->getEvent('IRCMessageInbound')->registerListener(array($this, 'channelMessageListener'));
		$this->evman()->getEvent('IRCMessageInbound')->registerListener(array($this, 'gateWatcher'));
		$this->evman()->getEvent('ChannelMessage')->registerListener(array($this, 'channelMessageLogger'));

			// Get the auth module.
		$this->auth = $this->bot->getModuleInstance('Auth');
	}

	/**
	 * The Join command.
	 * @param CommandEvent $e The last data received.
	 */
	public function joinCommand(CommandEvent $e)
	{
		if (empty($e->getParams()))
		{
			$this->bot->say('Not enough parameters. Usage: join [#channel] [#channel] [...]');
			return;
		}

		foreach ($e->getParams() as $chan)
		{
			if ($this->isInChannel($chan))
			{
				$this->bot->log('Not joining channel {channel} because I am already part of it.', array('channel' => $chan), LogLevels::CHANNEL);
				continue;
			}

			$this->channels[] = $chan;
			$this->joinChannel($chan);
		}
	}

	/**
	 * The Part command.
	 * @param CommandEvent $e The last data received.
	 */
	public function partCommand(CommandEvent $e)
	{
		// If no argument specified, attempt to leave the current channel.
		if (empty($e->getParams()))
			$c = array($e->getMessage()->getChannel());

		else
			$c = $e->getParams();

		foreach ($c as $chan)
		{
			$this->bot->sendData('PART ' . $chan);
		}
	}

	/**
	 * Join a channel.
	 * @param string $channel The channel name.
	 */
	public function joinChannel($channel)
	{
		if (empty($channel) || !Validation::isChannel($channel))
			return;

		$this->bot->sendData('JOIN ' . $channel);
	}

	/**
	 * This function handles the initial joining of channels.
	 * @param IRCMessageInboundEvent $e The last data received.
	 */
	public function initialJoin(IRCMessageInboundEvent $e)
	{
		// Are we ready?
		$status = $e->getMessage()->getCommand() == '376' && $e->getMessage()->get()['code'] == 'RPL_ENDOFMOTD';

		// And?
		if ($status)
		{
			$channels = $this->bot->getConfig('channels');

			foreach ($channels as $chan)
			{
				$this->joinChannel($chan);
			}
		}
	}

	/**
	 * This function handles raising channelMessage events.
	 * @param IRCMessageInboundEvent $e The last data received.
	 */
	public function channelMessageListener(IRCMessageInboundEvent $e)
	{
		if ($e->getMessage()->getCommand() != 'PRIVMSG')
			return;

		$message = new CommandPRIVMSG($e->getMessage(), $this->bot->getConfig('prefix'));

		$this->evman()->getEvent('ChannelMessage')->trigger(new ChannelMessageEvent($message->getTargets(), $message));
	}

	/**
	 * This function just logs data for channels.
	 * @param ChannelMessageEvent $e
	 */
	public function channelMessageLogger(ChannelMessageEvent $e)
	{
		$message = $e->getMessage();
		$umessage = $message->getUserMessage();

		if (substr($umessage, 0, 7) == chr(1) . 'ACTION')
		{
			$umessage = '*' . trim(substr(substr($umessage, 7), 0, -1)) . '*';
		}

		$this->bot->log(
			'({channel}) <{user}> {message}',
			array(
				'channel' => $message->getTargets(),
				'user' => $message->getNickname(),
				'message' => $umessage
			),
			LogLevels::CHANNEL);
	}

	/**
	 * This function watches for channel joins and parts, and keeps track of them.
	 * @param IRCMessageInboundEvent $e
	 */
	public function gateWatcher($e)
	{
		if (!empty($e->getMessage()->get()['code']) && $e->getMessage()->getCode() == 'RPL_TOPIC')
		{
			$channel = $e->getMessage()->getParams()[1];
			$this->bot->log('Joined channel {channel}', array('channel' => $channel), LogLevels::CHANNEL);
			$this->addChannel($channel);
			$this->evman()->getEvent('ChannelJoin')->trigger(new ChannelJoinEvent($channel));
		}

		if (($e->getMessage()->getCommand() == 'KICK' || $e->getMessage()->getCommand() == 'PART') && $e->getMessage()->getNickname() == $this->bot->getNickname())
		{
			$channel = $e->getMessage()->getParams()['channel'];
			$this->bot->log('Left channel {channel}', array('channel' => $channel), LogLevels::CHANNEL);
			$this->removeChannel($channel);
			$this->evman()->getEvent('ChannelPart')->trigger(new ChannelPartEvent($channel));
		}
	}

	/**
	 * Adds a channel to the list.
	 * @param string $channel
	 */
	public function addChannel($channel)
	{
		if (!in_array($channel, $this->channels))
			$this->channels[] = $channel;
	}

	/**
	 * Removes a channel from the list.
	 * @param string $channel
	 */
	public function removeChannel($channel)
	{
		if (in_array($channel, $this->channels))
			unset($this->channels[array_search($channel, $this->channels)]);
	}

	/**
	 * Checks if the bot is in a channel.
	 * @param string $channel
	 * @return boolean
	 */
	public function isInChannel($channel)
	{
		return in_array($channel, $this->channels);
	}

	/**
	 * List all channels the bot is in
	 * @return string[]
	 */
	public function listChannels()
	{
		return $this->channels;
	}
}
