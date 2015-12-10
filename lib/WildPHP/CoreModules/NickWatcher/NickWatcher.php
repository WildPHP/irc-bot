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

namespace WildPHP\CoreModules\NickWatcher;

use WildPHP\BaseModule;
use WildPHP\CoreModules\Connection\IrcDataObject;

class NickWatcher extends BaseModule
{
	/**
	 * @var string
	 */
	protected $nickname;

	/**
	 * @var string[]
	 */
	protected $queue = [];

	public function setup()
	{
		$events = [
			'listenError' => 'irc.data.in',
			'nickChanged' => 'irc.data.out.nick',
			'sendInitial' => 'irc.connection.created'
		];

		foreach ($events as $func => $event)
		{
			$this->getEventEmitter()->on($event, [$this, $func]);
		}

		$configuration = $this->getModulePool()->get('Configuration');
		$this->queue = $configuration->get('alternative-nicks');
	}

	public function sendInitial()
	{
		$connection = $this->getModulePool()->get('Connection');
		$configuration = $this->getModulePool()->get('Configuration');

		$connection->write($connection->getGenerator()->ircNick($configuration->get('nick')));
	}

	public function listenError(IrcDataObject $resource)
	{
		$logger = $this->getModulePool()->get('Logger');
		switch ($resource->getCommand())
		{
			case 431:
				$logger->warning('The server found the sent nickname to be empty. Trying alternatives...');

				break;

			case 432:
				$logger->warning('The set nickname contains invalid characters according to the server. Trying alternatives...');

				break;

			case 433:
				$logger->warning('The server reported that this nickname was already in use. Trying alternatives...');

				break;

			case 437:
				$logger->warning('The server reported that the sent nickname has been temporarily disabled. Trying alternative in the meantime.');

				break;
		}
	}

	public function tryAlternative()
	{
		$logger = $this->getModulePool()->get('Logger');

		if (empty($this->queue))
		{
			$logger->warning('No alternative nicknames have been set.');

			return;
		}

		if (empty(next($this->queue)))
		{
			$logger->debug('Alternative nickname queue exhausted. Resetting queue.');
			reset($this->queue);
		}
	}

	public function nickChanged($data)
	{
		$logger = $this->getModulePool()->get('Logger');
		$logger->info('Nickname change detected; old: ' . $this->getNickname() . '; new: ' . $data['params']['nickname']);
		$this->setNickname($data['params']['nickname']);

		$configuration = $this->getModulePool()->get('Configuration');
		$configuration->set('nick', $data['params']['nickname']);
	}

	/**
	 * @return string
	 */
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * @param string $nickname
	 */
	protected function setNickname($nickname)
	{
		$this->nickname = $nickname;
	}
}