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
				$this->evman()->getEvent('BotCommand')->registerCommand('op', array($this, 'opCommand'), true);
				$this->evman()->getEvent('BotCommand')->registerCommand('deop', array($this, 'deOpCommand'), true);
				$this->evman()->getEvent('BotCommand')->registerCommand('voice', array($this, 'voiceCommand'), true);
				$this->evman()->getEvent('BotCommand')->registerCommand('devoice', array($this, 'deVoiceCommand'), true);
				$this->evman()->getEvent('BotCommand')->registerCommand('kick', array($this, 'kickCommand'), true);

				// Get the auth module.
				$this->auth = $this->bot->getModuleInstance('Auth');
		}

		/**
		 * The OP command.
		 * @param CommandEvent $e The last data received.
		 */
		public function opCommand($e)
		{
				if (empty($e->getParams()))
				{
					$this->bot->say('Not enough parameters. Usage: op [#channel] [user] or op [user]');
					return;
				}

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getMessage()->getTargets();
				
				// OPs Selected Person.
				$this->bot->sendData('MODE ' . $chan . ' +o ' . implode(' ', $parts));
		}

		/**
		 * The De-OP command.
		 * @param CommandEvent $e The last data received.
		 */
		public function deOpCommand($e)
		{
				if (empty($e->getParams()))
				{
					$this->bot->say('Not enough parameters. Usage: deop [#channel] [user] or deop [user]');
					return;
				}

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getMessage()->getTargets();
				$this->bot->sendData('MODE ' . $chan . ' -o ' . implode(' ', $parts));
		}

		/**
		 * The Voice command.
		 * @param CommandEvent $e The last data received.
		 */
		public function voiceCommand($e)
		{
				if (empty($e->getParams()))
				{
					$this->bot->say('Not enough parameters. Usage: voice [#channel] [user] or voice [user]');
					return;
				}

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getMessage()->getTargets();
				$this->bot->sendData('MODE ' . $chan . ' +v ' . implode(' ', $parts));
		}

		/**
		 * The De-Voice command.
		 * @param CommandEvent $e The last data received.
		 */
		public function deVoiceCommand($e)
		{
				if (empty($e->getParams()))
				{
					$this->bot->say('Not enough parameters. Usage: devoice [#channel] [user] or devoice [user]');
					return;
				}

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getMessage()->getTargets();
				$this->bot->sendData('MODE ' . $chan . ' -v ' . implode(' ', $parts));
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
}