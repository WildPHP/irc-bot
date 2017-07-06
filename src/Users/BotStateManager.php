<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;

use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;

class BotStateManager extends BaseModule
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
		EventEmitter::fromContainer($container)
			->on('user.kick', [$this, 'cleanupChannel']);
		$this->setContainer($container);
	}

	/**
	 * @param User $user
	 * @param Channel $channel
	 */
	public function cleanupChannel(User $user, Channel $channel)
	{
		/** @var User $botUserObject */
		$botUserObject = UserCollection::fromContainer($this->getContainer())->getSelf();

		if ($user != $botUserObject)
			return;

		Logger::fromContainer($this->getContainer())->debug('Cleaning up channel', [
			'channel' => $channel->getName()
		]);

		$users = UserCollection::fromContainer($this->getContainer())->values();

		/** @var User $user */
		foreach ($users as $user)
		{
			$channelCollection = $user->getChannelCollection();

			if (!$channelCollection->contains($channel))
				continue;

			$channelCollection->removeAll($channel);

			Logger::fromContainer($this->getContainer())->debug('Removed channel for user', [
				'reason' => 'botParted',
				'nickname' => $user->getNickname(),
				'channel' => $channel->getName()
			]);
		}

		if ($botUserObject->getChannelCollection()->contains($channel))
			$botUserObject->getChannelCollection()->removeAll($channel);
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

		Configuration::fromContainer($this->getContainer())['currentNickname'] = $newNickname;

		Logger::fromContainer($this->getContainer())->debug('Updated current nickname for bot', [
			'oldNickname' => $oldNickname,
			'newNickname' => $newNickname
		]);
	}
}