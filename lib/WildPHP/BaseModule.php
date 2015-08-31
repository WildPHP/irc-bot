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

namespace WildPHP;

use WildPHP\EventManager\InvalidEventTypeException;
use WildPHP\EventManager\RegisteredCommandEvent;
use WildPHP\Modules\Help;

class BaseModule
{
	/**
	 * The module directory.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * The Api.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Set up the module.
	 *
	 * @param Api $api The current Api.
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
		$dirname = explode('\\', get_class($this));
		$this->dir = WPHP_MODULE_DIR . '/' . end($dirname) . '/';
	}

	/**
	 * Init the module.
	 */
	public function init()
	{
		if (method_exists($this, 'setup'))
		{
			$result = $this->setup();

			if ($result === false)
			{
				$this->api->getLogger()->debug('Module initialisation canceled. The module will remain loaded.');
				return;
			}
		}

		// Set up predefined events, then.
		/*if (method_exists($this, 'registerEvents'))
			$this->handleEvents();

		// And commands.
		if (method_exists($this, 'registerCommands'))
			$this->handleCommands();*/
	}

	/**
	 * Return registered listeners.
	 *
	 * @return array
	 */
	public function getListeners()
	{
		if (!method_exists($this, 'registerEvents'))
			return [];

		return $this->registerEvents();
	}

	/**
	 * Return registered commands.
	 *
	 * @return array
	 */
	public function getCommands()
	{
		if (!method_exists($this, 'registerCommands'))
			return [];

		return array_keys($this->registerCommands());
	}

	/**
	 * Handle event registering.
	 */
	/*private function handleEvents()
	{
		if (!method_exists($this, 'registerEvents'))
			throw new \RuntimeException('You may not call BaseModule::handleEvents when the module itself has no registerEvents method.');

		$events = $this->registerEvents();
		if (!is_array($events))
			throw new \InvalidArgumentException('BaseModule::handleEvents expects registerEvents to return an array in the format of \'callback\' => \'event\', ' . gettype($events) . ' given.');

		foreach ($events as $callback => $event)
		{
			if (!is_callable([$this, $callback]))
				throw new \InvalidArgumentException('Please make sure the methods exist for the predefined event map in your module.');

			if (!$this->getEventManager()->isRegistered($event))
				throw new \InvalidArgumentException('Please make sure you are mapping to existing and registered events. If you need to register an event, do so in your module\'s setup method.');

			$this->getEventManager()->getEvent($event)->registerListener([$this, $callback], $this);
		}
	}*/

	/**
	 * Handle command registering.
	 */
	/*private function handleCommands()
	{
		if (!method_exists($this, 'registerCommands'))
			throw new \RuntimeException('You may not call BaseModule::handleCommands when the module itself has no registerCommands method.');

		// First make sure the CommandParser module is loaded.
		$this->getModule('CommandParser');
		$cmds = $this->registerCommands();
		if (!is_array($cmds))
			throw new \InvalidArgumentException('BaseModule::handleCommands expects registerCommands to return an array in the format of \'command\' => array(\'callback\' => callback, [\'help\' => string], [\'auth\' => boolean]), ' . gettype($cmds) . ' given.');

		$botCommand = $this->getEventManager()->getEvent('BotCommand');

		if (!($botCommand instanceof RegisteredCommandEvent))
			throw new InvalidEventTypeException('BaseModule::handleCommands expects event BotCommand to be of type RegisteredCommandEvent, got ' . gettype($botCommand));

		$help = $this->getModule('Help');

		if (!($help instanceof Help))
			throw new \RuntimeException('BaseModule could not find the Help module.');

		foreach ($cmds as $command => $data)
		{
			if (empty($data) || empty($data['callback']) || !is_callable([$this, $data['callback']]))
				throw new \InvalidArgumentException('registerCommands returned invalid result.');

			$botCommand->registerCommand($command, [$this, $data['callback']], $this, !empty($data['auth']));

			if (!empty($data['help']))
				$help->registerHelp($command, $data['help']);
		}
	}*/

	/**
	 * Return the working directory of this module.
	 *
	 * @return string
	 */
	public function getWorkingDir()
	{
		return $this->dir;
	}
}