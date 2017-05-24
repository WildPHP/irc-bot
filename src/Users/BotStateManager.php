<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Users;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\ConfigurationItem;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;

class BotStateManager
{
	use ContainerTrait;

	public function __construct(ComponentContainer $container)
	{
		EventEmitter::fromContainer($container)->on('user.nick', [$this, 'monitorOwnNickname']);
		$this->setContainer($container);
	}

	public function monitorOwnNickname(User $user, string $oldNickname, string $newNickname, Queue $queue)
	{
		if ($user != UserCollection::fromContainer($this->getContainer())->getSelf())
			return;

		$configurationItem = new ConfigurationItem('currentNickname', $newNickname);
		Configuration::fromContainer($this->getContainer())->set($configurationItem);

		Logger::fromContainer($this->getContainer())->debug('Updated current nickname for bot', [
			'oldNickname' => $oldNickname,
			'newNickname' => $newNickname
		]);
	}
}