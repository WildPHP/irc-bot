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
namespace WildPHP\EventManager;

use WildPHP\BaseModule;
use WildPHP\Modules\Auth;
use WildPHP\Event\CommandEvent;
use WildPHP\Event\IEvent;

/**
 * Represents a registered event within the event manager.
*/
class RegisteredCommandEvent extends RegisteredEvent
{
	/**
	 * The Auth module.
	 * @var Auth
	 */
	protected $auth;

	/**
	 * The list of commands.
	 * @var array<string, array>
	 */
	protected $commands = array();

	/**
	 * Sets the authentication module for this event.
	 * @param BaseModule $auth
	 */
	public function setAuthModule(BaseModule $auth)
	{
		if ($this->auth instanceof Auth)
			throw new \InvalidArgumentException('Could not add Auth module to RegisteredCommandEvent; the specified module is not the Auth module.');

		$this->auth = $auth;
	}

	/**
	 * Registers a new command.
	 * @param string $command The command, like 'say'.
	 * @param callable $call The callable to call.
	 * @param bool $auth Whether the command needs pre-execution authentication.
	 * @throws \InvalidArgumentException when an invalid command is being added.
	 * @throws CommandExistsException when the command already exists.
	 */
	public function registerCommand($command, $call, $auth = false)
	{
		if (empty($command) || empty($call) || !is_callable($call))
			throw new \InvalidArgumentException();

		if ($this->commandExists($command))
			throw new CommandExistsException();

		$this->commands[$command] = array(
			'auth' => (bool) $auth,
			'call' => $call
		);
	}

	/**
	 * Removes a command.
	 * @param string $command
	 * @throws \InvalidArgumentException when $command is empty.
	 * @throws CommandDoesNotExistException when the command does not exist.
	 */
	public function removeCommand($command)
	{
		if (empty($command))
			throw new \InvalidArgumentException();

		if (!$this->commandExists($command))
			throw new CommandDoesNotExistException();

		unset($this->commands[$command]);
	}

	/**
	 * Checks if a command already exists.
	 * @param string $command
	 * @return boolean
	 */
	public function commandExists($command)
	{
		return array_key_exists($command, $this->commands);
	}

	/**
	 * Triggers the event.
	 * @param IEvent $event The event that gets passed to the listeners.
	 * @throws \InvalidArgumentException when an invalid event type is passed.
	 * @return void
	 */
	public function trigger(IEvent $event)
	{
		if (!($event instanceof CommandEvent))
			throw new \InvalidArgumentException('For triggering a RegisteredCommandEvent, you need to pass a CommandEvent.');

		if (!$this->commandExists($event->getCommand()))
			return;

		// Needs authorization?
		if ($this->commands[$event->getCommand()]['auth'] && !$this->auth->authUser($event->getMessage()->getHostname()))
				return;

		// Do the call.
		call_user_func($this->commands[$event->getCommand()]['call'], $event);
	}

	/**
	 * List all available commands.
	 * @return string[]
	 */
	public function getCommands()
	{
		return array_keys($this->commands);
	}
}
