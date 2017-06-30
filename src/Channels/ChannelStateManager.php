<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Channels;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\PART;
use WildPHP\Core\Connection\IRCMessages\RPL_NAMREPLY;
use WildPHP\Core\Connection\IRCMessages\RPL_TOPIC;
use WildPHP\Core\Connection\IRCMessages\RPL_WELCOME;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class ChannelStateManager
{
	use ContainerTrait;

	/**
	 * ChannelStateManager constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$events = [
			'irc.line.in.join' => 'processUserJoin',
			'irc.line.in.part' => 'processUserPart',
			'irc.line.in.kick' => 'processUserKick',
			'user.quit' => 'processUserQuit',
			'user.mode.channel' => 'processUserModeChange',

			// 353: RPL_NAMREPLY
			'irc.line.in.353' => 'populateChannel',

			// 332: RPL_TOPIC
			'irc.line.in.332' => 'processChannelTopicChange',

			// 001: RPL_WELCOME
			'irc.line.in.001' => 'joinInitialChannels',
		];

		foreach ($events as $event => $callback)
		{
			EventEmitter::fromContainer($container)
				->on($event, [$this, $callback]);
		}

		$this->setContainer($container);
	}

	/**
	 * @param JOIN $ircMessage
	 * @param Queue $queue
	 */
	public function processUserJoin(JOIN $ircMessage, Queue $queue)
	{
		$prefix = $ircMessage->getPrefix();
		/** @var User $userObject */
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findOrCreateByNickname($ircMessage->getNickname());

		/** @var Channel $channel */
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->requestByChannelName($ircMessage->getChannels()[0], $userObject);
		$accountname = $ircMessage->getIrcAccount();

		// TODO Isn't this really UserStateManager's job?
		$userObject->setIrcAccount($accountname);
		$userObject->setHostname($prefix->getHostname());
		$userObject->setUsername($prefix->getUsername());
		$userObject->getChannelCollection()
			->append($channel);

		$channel->getUserCollection()
			->append($userObject);

		EventEmitter::fromContainer($this->getContainer())
			->emit('user.join', [$userObject, $channel, $queue]);

		Logger::fromContainer($this->getContainer())
			->debug('Added user to channel.',
				[
					'reason' => 'join',
					'nickname' => $userObject->getNickname(),
					'channel' => $channel->getName()
				]);
	}

	/**
	 * @param PART $ircMessage
	 * @param Queue $queue
	 */
	public function processUserPart(PART $ircMessage, Queue $queue)
	{
		/** @var Channel $channel */
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($ircMessage->getChannels()[0]);

		/** @var User $userObject */
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($ircMessage->getNickname());

		if (!$channel->getUserCollection()->contains($userObject))
			return;

		$channel->getUserCollection()->removeAll($userObject);
		$userObject->getChannelCollection()->removeAll($channel);

		EventEmitter::fromContainer($this->getContainer())
			->emit('user.part', [$userObject, $channel, $queue]);

		Logger::fromContainer($this->getContainer())
			->debug('Removed user from channel',
				[
					'reason' => 'part',
					'nickname' => $userObject->getNickname(),
					'channel' => $channel->getName()
				]);
	}

	/**
	 * @param KICK $ircMessage
	 * @param Queue $queue
	 */
	public function processUserKick(KICK $ircMessage, Queue $queue)
	{
		/** @var Channel $channel */
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($ircMessage->getChannel());

		/** @var User $userObject */
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($ircMessage->getTarget());

		if (!$channel->getUserCollection()->contains($userObject))
			return;

		$channel->getUserCollection()->removeAll($userObject);
		$userObject->getChannelCollection()->removeAll($channel);

		EventEmitter::fromContainer($this->getContainer())
			->emit('user.kick', [$userObject, $channel, $queue]);

		Logger::fromContainer($this->getContainer())
			->debug('Removed user from channel',
				[
					'reason' => 'kick',
					'nickname' => $userObject->getNickname(),
					'channel' => $channel->getName()
				]);
	}

	/**
	 * @param User $userObject
	 */
	public function processUserQuit(User $userObject)
	{
		/** @var Channel[] $channels */
		$channels = ChannelCollection::fromContainer($this->getContainer())
			->values();

		/** @var Channel $channel */
		foreach ($channels as $channel)
		{
			if (!$channel->getUserCollection()->contains($userObject) || !$userObject->getChannelCollection()->contains($channel))
				continue;

			$channel->getUserCollection()->removeAll($userObject);
			$userObject->getChannelCollection()->removeAll($channel);

			Logger::fromContainer($this->getContainer())
				->debug('Removed user from channel',
					[
						'reason' => 'quit',
						'nickname' => $userObject->getNickname(),
						'channel' => $channel->getName()
					]);
		}
	}

	/**
	 * @param string $channel
	 * @param string $mode
	 * @param User $target
	 */
	public function processUserModeChange(string $channel, string $mode, User $target)
	{
		$shouldBeRemoved = substr($mode, 0, 1) == '-';
		$modes = substr($mode, 1);
		$modes = str_split($modes);

		/** @var Channel $channel */
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($channel);

		foreach ($modes as $mode)
		{
			if ($shouldBeRemoved)
				$channel->getChannelModes()
					->removeUserFromMode($mode, $target);
			else
				$channel->getChannelModes()
					->addUserToMode($mode, $target);
		}

		Logger::fromContainer($this->getContainer())
			->debug('Updated mode for user',
				[
					'channel' => $channel->getName(),
					'nickname' => $target->getNickname(),
					'diff' => $modes,
					'newmodes' => $channel->getChannelModes()
						->getModesForUser($target)
				]);
	}

	/**
	 * @param RPL_NAMREPLY $ircMessage
	 */
	public function populateChannel(RPL_NAMREPLY $ircMessage)
	{
		$channel = $ircMessage->getChannel();
		/** @var Channel $channel */
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->requestByChannelName($channel);
		$nicknames = $ircMessage->getNicknames();

		foreach ($nicknames as $nicknameWithMode)
		{
			$nickname = $nicknameWithMode;
			$modes = $channel->getChannelModes()
				->extractUserModesFromNickname($nicknameWithMode, $nickname);

			/** @var User $userObject */
			$userObject = UserCollection::fromContainer($this->getContainer())
				->findOrCreateByNickname($nickname);

			if (!empty($modes))
			{
				foreach ($modes as $mode)
				{
					$channel->getChannelModes()
						->addUserToMode($mode, $userObject);
				}
			}

			if (!$userObject->getChannelCollection()
				->findByChannelName($channel->getName())
			)
				$userObject->getChannelCollection()
					->append($channel);

			if (!$channel->getUserCollection()
				->findByNickname($userObject->getNickname())
			)
				$channel->getUserCollection()
					->append($userObject);

			Logger::fromContainer($this->getContainer())
				->debug('Added user to channel', [
					'reason' => 'initialJoin',
					'nickname' => $userObject->getNickname(),
					'channel' => $channel->getName()
				]);
		}
	}

	/**
	 * @param RPL_TOPIC $ircMessage
	 */
	public function processChannelTopicChange(RPL_TOPIC $ircMessage)
	{
		$channel = $ircMessage->getChannel();

		/** @var Channel $channel */
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($channel);

		if (!$channel)
			return;

		$channel->setTopic($ircMessage->getMessage());
		EventEmitter::fromContainer($this->getContainer())
			->emit('channel.topic', [$channel, $ircMessage->getMessage()]);
	}

	/**
	 * @param RPL_WELCOME $incomingIrcMessage
	 * @param Queue $queue
	 */
	public function joinInitialChannels(RPL_WELCOME $incomingIrcMessage, Queue $queue)
	{
		$channels = Configuration::fromContainer($this->getContainer())['channels'];

		if (empty($channels))
			return;

		$chunks = array_chunk($channels, 3);
		$queue->setFloodControl(true);

		foreach ($chunks as $chunk)
		{
			$queue->join($chunk);
		}

		Logger::fromContainer($this->getContainer())
			->debug('Queued initial channel join.',
				[
					'count' => count($channels),
					'channels' => $channels
				]);
	}
}