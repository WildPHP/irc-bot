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
use WildPHP\IRC\CommandPRIVMSG;

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
				$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'opCommand'));
				$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'deOpCommand'));
				$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'voiceCommand'));
				$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'deVoiceCommand'));
				$this->evman()->getEvent('BotCommand')->registerListener(array($this, 'kickCommand'));

				// Get the auth module.
				$this->auth = $this->bot->getModuleInstance('Auth');
		}

		/**
		 * The OP command.
		 * @param CommandPRIVMSG $e The last data received.
		 */
		public function opCommand($e)
		{
				if ($e->getCommand() != 'op' || empty($e->getParams()) || !$this->auth->authUser($e->getSender()))
						return;

				$parts = $e->getParams();
				
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getTargets();
				
				// OPs Selected Person.
				$this->bot->sendData('MODE ' . $chan . ' +o ' . implode(' ', $parts));
		}

		/**
		 * The De-OP command.
		 * @param CommandPRIVMSG $e The last data received.
		 */
		public function deOpCommand($e)
		{
				if ($e->getCommand() != 'deop' || empty($e->getParams()) || !$this->auth->authUser($e->getSender()))
						return;

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getTargets();
				$this->bot->sendData('MODE ' . $chan . ' -o ' . implode(' ', $parts));
		}

		/**
		 * The Voice command.
		 * @param CommandPRIVMSG $e The last data received.
		 */
		public function voiceCommand($e)
		{
				if ($e->getCommand() != 'voice' || empty($e->getParams()) || !$this->auth->authUser($e->getSender()))
						return;

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getTargets();
				$this->bot->sendData('MODE ' . $chan . ' +v ' . implode(' ', $parts));
		}

		/**
		 * The De-Voice command.
		 * @param CommandPRIVMSG $e The last data received.
		 */
		public function deVoiceCommand($e)
		{
				if ($e->getCommand() != 'devoice' || empty($e->getParams()) || !$this->auth->authUser($e->getSender()))
						return;

				$parts = $e->getParams();
				if (Validation::isChannel($parts[0]))
						$chan = array_shift($parts);
				else
						$chan = $e->getTargets();
				$this->bot->sendData('MODE ' . $chan . ' -v ' . implode(' ', $parts));
		}
        
		/**
		 * The Kick command.
		 * @param CommandPRIVMSG $e The last data received.
		 */
		public function kickCommand($e)
		{
				if ($e->getCommand() != 'kick' || empty($e->getParams()) || !$this->auth->authUser($e->getSender()))
						return;
				
				$cdata = explode(' ', $e->getParams());
                
				if(count($cdata) < 2)
						return;

				$user = array_shift($cdata);
				$this->bot->sendData('KICK ' . $e->getParams()[0] . ' ' . $user . ' :' . implode(' ', $cdata));
		}
}