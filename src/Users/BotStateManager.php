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

use WildPHP\Core\Channels\Channel;
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

	/**
	 * BotStateManager constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		EventEmitter::fromContainer($container)
			->on('user.nick', [$this, 'monitorOwnNickname']);
		EventEmitter::fromContainer($container)
			->on('user.part', [$this, 'cleanupChannel']);
		$this->setContainer($container);
	}

	/**
	 * @param User $user
	 * @param Channel $channel
	 */
	public function cleanupChannel(User $user, Channel $channel)
	{
		$botUserObject = UserCollection::fromContainer($this->getContainer())->getSelf();

		if ($user !== $botUserObject)
			return;

		Logger::fromContainer($this->getContainer())->debug('Cleaning up channel', [
			'channel' => $channel->getName()
		]);

		$users = UserCollection::fromContainer($this->getContainer())->toArray();

		/** @var User $user */
		foreach ($users as $user)
		{
			$channelCollection = $user->getChannelCollection();
			$result = $channelCollection->remove(function (Channel $userChannel) use ($channel)
			{
				return $channel === $userChannel;
			});

			if ($result)
				Logger::fromContainer($this->getContainer())->debug('Removed channel for user', [
					'reason' => 'botParted',
					'nickname' => $user->getNickname(),
					'channel' => $channel->getName()
				]);
		}
	}

	/**
	 * @param User $user
	 * @param string $oldNickname
	 * @param string $newNickname
	 * @param Queue $queue
	 */
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