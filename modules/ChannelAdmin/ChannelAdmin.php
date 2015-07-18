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

class ChannelAdmin extends BaseModule
{
		/**
		 * The Auth module's object.
		 * @var \WildPHP\Modules\Auth
		 */
		private $auth;

		/**
		 * Dependencies of this module.
		 * @var string[]
		 */
		protected static $dependencies = array('Auth', 'ChannelManager');

		/**
		 * Set up the module.
		 */
		public function setup()
		{
				// Register our commands.
				$botCommand = $this->evman()->getEvent('BotCommand');
				$botCommand->registerCommand('op', array($this, 'opCommand'), true);
				$botCommand->registerCommand('deop', array($this, 'deOpCommand'), true);
				$botCommand->registerCommand('voice', array($this, 'voiceCommand'), true);
				$botCommand->registerCommand('devoice', array($this, 'deVoiceCommand'), true);
				$botCommand->registerCommand('kick', array($this, 'kickCommand'), true);

				// Get the auth module.
				$this->auth = $this->bot->getModuleInstance('Auth');
		}

		/**
		 * The OP command.
		 * @param CommandEvent $e The last data received.
		 */
		public function opCommand($e)
		{
				if (($chan = $this->parseChannel($e)) === false)
						$this->bot->say('Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]');

				$this->bot->sendData('MODE ' . $chan . ' +o ' . implode(' ', $e->getParams()));
		}

		/**
		 * The De-OP command.
		 * @param CommandEvent $e The last data received.
		 */
		public function deOpCommand($e)
		{
				if (($chan = $this->parseChannel($e)) === false)
						$this->bot->say('Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]');

				$this->bot->sendData('MODE ' . $chan . ' -o ' . implode(' ', $e->getParams()));
		}

		/**
		 * The Voice command.
		 * @param CommandEvent $e The last data received.
		 */
		public function voiceCommand($e)
		{
				if (($chan = $this->parseChannel($e)) === false)
						$this->bot->say('Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]');

				$this->bot->sendData('MODE ' . $chan . ' +v ' . implode(' ', $e->getParams()));
		}

		/**
		 * The De-Voice command.
		 * @param CommandEvent $e The last data received.
		 */
		public function deVoiceCommand($e)
		{
				if (($chan = $this->parseChannel($e)) === false)
						$this->bot->say('Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]');

				$this->bot->sendData('MODE ' . $chan . ' -v ' . implode(' ', $e->getParams()));
		}

		/**
		 * The Kick command.
		 * @param CommandEvent $e The last data received.
		 */
		public function kickCommand($e)
		{
				if (empty($e->getParams()))
				{
					$this->bot->say('Not enough parameters. Usage: kick [user]');
					return;
				}

				$cdata = explode(' ', $e->getParams());

				if(count($cdata) < 2)
						return;

				$user = array_shift($cdata);
				$this->bot->sendData('KICK ' . $e->getParams()[0] . ' ' . $user . ' :' . implode(' ', $cdata));
		}

		/**
		 * Try to get a channel out of the gathered data.
		 * @param CommandEvent $e The last data received.
		 * @return boolean|string|string[] False on failure, channel as string on success.
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
