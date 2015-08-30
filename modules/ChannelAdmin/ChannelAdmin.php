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
use WildPHP\Event\IRCMessageInboundEvent;
use WildPHP\Modules\ChannelAdmin\Kick;
use WildPHP\Modules\ChannelAdmin\Mode;
use WildPHP\Validation;
use WildPHP\Modules\CommandParser\Event\CommandEvent;
use WildPHP\IRC\Commands\Privmsg;

/**
 * Class ChannelAdmin
 *
 * @package WildPHP\Modules
 */
class ChannelAdmin extends BaseModule
{
	/**
	 * The Auth module's object.
	 *
	 * @var \WildPHP\Modules\Auth
	 */
	private $auth;

	/**
	 * Valid channel modes for this server.
	 *
	 * @var array
	 */
	protected $validModes = [];

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Get the auth module.
		$this->auth = $this->getModule('Auth');
	}

	/**
	 * Register our commands.
	 *
	 * @return array
	 */
	public function registerCommands()
	{
		return [
			'op'      => [
				'callback' => 'opCommand',
				'help'     => 'OPs a user. Usage: op [channel|user] [user] [user] [...]',
				'auth'     => true
			],
			'deop'    => [
				'callback' => 'deOpCommand',
				'help'     => 'Undos an OP flag. Usage: deop [channel|user] [user] [user] [...]',
				'auth'     => true
			],
			'voice'   => [
				'callback' => 'voiceCommand',
				'help'     => 'Voices a user. Usage: voice [channel|user] [user] [user] [...]',
				'auth'     => true
			],
			'devoice' => [
				'callback' => 'deVoiceCommand',
				'help'     => 'Undos a voice flag. Usage: devoice [channel|user] [user] [user] [...]',
				'auth'     => true
			],
			'kick'    => [
				'callback' => 'kickCommand',
				'help'     => 'Kicks a user from the channel. Usage: kick [channel]',
				'auth'     => true
			],
			/*'mode'    => [
				'callback' => 'modeCommand',
				'help'     => 'sets a mode for the current channel. Usage: mode [modes] ([arguments])',
				'auth'     => true
			]*/
		];
	}

	/**
	 * Register our events.
	 *
	 * @return array
	 */
	public function registerEvents()
	{
		return [
			'parseModes' => 'IRCMessageInbound'
		];
	}

	/**
	 * Parse modes from incoming 005's.
	 *
	 * @param IRCMessageInboundEvent $e
	 */
	public function parseModes(IRCMessageInboundEvent $e)
	{
		// We can't use messages other than 005.
		if ($e->getMessage()->getCommand() != '005')
			return;

		foreach ($e->getMessage()->getParams() as $param)
		{
			if (substr($param, 0, 9) != 'CHANMODES')
				continue;

			$this->validModes = array_filter(str_split(str_replace('CHANMODES=', '', $param)), [$this, 'filterModes']);
			break;
		}
	}

	/**
	 * Filters all commas from the list of valid modes. Use with array_filter.
	 *
	 * @param string $mode
	 * @return string[]
	 */
	public function filterModes($mode)
	{
		return $mode != ',';
	}

	/**
	 * The OP command.
	 *
	 * @param CommandEvent $e The last data received.
	 */
	public function opCommand($e)
	{
		if (($chan = $this->parseChannel($e)) === false)
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]'));

		$this->sendData(new Mode('+o', $chan, implode(' ', $e->getParams())));
	}

	/**
	 * The De-OP command.
	 *
	 * @param CommandEvent $e The last data received.
	 */
	public function deOpCommand($e)
	{
		if (($chan = $this->parseChannel($e)) === false)
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]'));

		$this->sendData(new Mode('-o', $chan, implode(' ', $e->getParams())));
	}

	/**
	 * The Voice command.
	 *
	 * @param CommandEvent $e The last data received.
	 */
	public function voiceCommand($e)
	{
		if (($chan = $this->parseChannel($e)) === false)
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]'));

		$this->sendData(new Mode('+v', $chan, implode(' ', $e->getParams())));
	}

	/**
	 * The De-Voice command.
	 *
	 * @param CommandEvent $e The last data received.
	 */
	public function deVoiceCommand($e)
	{
		if (($chan = $this->parseChannel($e)) === false)
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]'));

		$this->sendData(new Mode('-v', $chan, implode(' ', $e->getParams())));
	}

	/**
	 * The Kick command.
	 *
	 * @param CommandEvent $e The last data received.
	 */
	public function kickCommand($e)
	{
		if (empty($e->getParams()))
		{
			$this->sendData(new Privmsg($this->getLastChannel(), 'Not enough parameters. Usage: kick [user] ([message])'));
			return;
		}

		$cdata = explode(' ', $e->getParams());

		if (count($cdata) < 2)
			return;

		$user = array_shift($cdata);
		$this->sendData(new Kick($e->getParams()[0], $user, implode(' ', $cdata)));
	}

	/**
	 * Try to get a channel out of the gathered data.
	 *
	 * @param CommandEvent $e The last data received.
	 * @return false|string False on failure, channel as string on success.
	 */
	public function parseChannel($e)
	{
		if (empty($e->getParams()))
			return false;

		$parts = $e->getParams();
		if (Validation::isChannel($parts[0]))
			$chan = array_shift($parts);
		else
			$chan = $e->getMessage()->getTargets();

		if ($chan === null)
			return false;

		return $chan;
	}
}
