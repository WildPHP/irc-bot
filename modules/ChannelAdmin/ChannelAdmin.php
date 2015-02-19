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

use WildPHP\Bot;

class ChannelAdmin
{
        /**
         * The Bot object. Used to interact with the main thread.
         * @var \WildPHP\Core\Bot
         */
        private $bot;

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
        public function __construct(Bot $bot)
        {
                $this->bot = $bot;

                // Get the event manager over here.
                $this->evman = $this->bot->getEventManager();

                // Register our commands.
                $this->evman->registerEvent(array('command_op', 'command_deop', 'command_voice', 'command_devoice', 'command_kick'), array('hook_once' => true));                
                $this->evman->registerEventListener('command_op', array($this, 'OPCommand'));
                $this->evman->registerEventListener('command_deop', array($this, 'DeOPCommand'));
                $this->evman->registerEventListener('command_voice', array($this, 'VoiceCommand'));
                $this->evman->registerEventListener('command_devoice', array($this, 'DeVoiceCommand'));
                $this->evman->registerEventListener('command_kick', array($this, 'KickCommand'));

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
         * The OP command.
         * @param array $data The last data received.
         */
        public function OPCommand($data)
        {
                if (empty($data['string']))
                        return;

                if (!$this->auth->authUser($data['hostname']))
                        return;

                // OPs Selected Person.

                $this->bot->sendData('MODE ' . $data['arguments'][0] . ' +o ' . $data['command_arguments']);
        }

        /**
         * The De-OP command.
         * @param array $data The last data received.
         */
        public function DeOPCommand($data)
        {
                if (empty($data['string']))
                        return;

                if (!$this->auth->authUser($data['hostname']))
                        return;

                $this->bot->sendData('MODE ' . $data['arguments'][0] . ' -o ' . $data['command_arguments']);
        }

        /**
         * The Voice command.
         * @param array $data The last data received.
         */
        public function VoiceCommand($data)
        {
                if (empty($data['string']))
                        return;

                if (!$this->auth->authUser($data['hostname']))
                        return;


                $this->bot->sendData('MODE ' . $data['arguments'][0] . ' +v ' . $data['command_arguments']);
        }

        /**
         * The De-Voice command.
         * @param array $data The last data received.
         */
        public function DeVoiceCommand($data)
        {
                if (empty($data['string']))
                        return;
                        
                if (!$this->auth->authUser($data['hostname']))
                        return;


                $this->bot->sendData('MODE ' . $data['arguments'][0] . ' -v ' . $data['command_arguments']);
        }
        
        /**
         * The Kick command.
         * @param array $data The last data received.
         */
        public function KickCommand($data)
        {
                if (empty($data['string']))
                        return;

                if (!$this->auth->authUser($data['hostname']))
                        return;
                
                $cdata = explode(' ', $data['command_arguments']);
                
                if (count($cdata) < 2)
                        return;

                $user = array_shift($cdata);
                $this->bot->sendData('KICK ' . $data['arguments'][0] . ' ' . $user . ' :' . implode(' ', $cdata));
        }
}