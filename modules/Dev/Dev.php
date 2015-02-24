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

class Dev
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
     * Has the Exec command been explicitly unlocked?
     * @var boolean
     */
    private $unlockExec = false;

    /**
     * Set up the module.
     * @param object $bot The Bot object.
     */
    public function __construct($bot)
    {
        $this->bot = $bot;

        // Get the event manager over here.
        $this->evman = $this->bot->getEventManager();

        // Register our command.
        $this->evman->registerEvent(array('command_exec'), array('hook_once' => true));
        $this->evman->registerEventListener('command_exec', array($this, 'ExecCommand'));

        // Get the auth module in here.
        $this->auth = $this->bot->getModuleInstance('Auth');
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
     * Executes a command.
     * @param array $data The data received.
     * @return bool
     */
    public function ExecCommand($data)
    {
        if(!$this->auth->authUser($data['hostname']))
        {
            $this->bot->say('You are not authorized to execute this command.');
            return false;
        }

        if(!$this->unlockExec && $data['command_arguments'] != '$this->unlockExec = true;')
        {
            $this->bot->say($data['nickname'], 'WARNING: The Exec command is a VERY DANGEROUS COMMAND, and is therefore locked by default.');
            $this->bot->say($data['nickname'], 'To unlock this command and understand that any damage caused by the use of this command relies exclusively on YOU and NOT THE BOT AUTHORS,');
            $this->bot->say($data['nickname'], 'Run the following command: ' . $this->bot->getConfig('prefix') . 'exec $this->unlockExec = true;');
            return false;
        }
        elseif(!$this->unlockExec && $data['command_arguments'] != '$this->unlockExec = true;')
            $this->bot->say($data['nickname'], 'The Exec command will now be unlocked.');

        $this->bot->log('Running command "' . $data['command_arguments'] . '"');
        eval($data['command_arguments']);
        return true;
    }
}