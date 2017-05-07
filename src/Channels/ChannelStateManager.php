<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 26-4-2017
 * Time: 16:11
 */

namespace WildPHP\Core\Channels;


use Flintstone\Config;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\PART;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\UserPrefix;
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
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findOrCreateByNickname($ircMessage->getNickname());

		$channel = ChannelCollection::fromContainer($this->getContainer())
			->requestByChannelName($ircMessage->getChannels()[0], $userObject);
		$accountname = $ircMessage->getIrcAccount();

		// TODO Isn't this really UserStateManager's job?
		$userObject->setIrcAccount($accountname);
		$userObject->setHostname($prefix->getHostname());
		$userObject->setUsername($prefix->getUsername());
		$userObject->getChannelCollection()
			->add($channel);

		$channel->getUserCollection()
			->add($userObject);

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
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($ircMessage->getChannels()[0]);
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($ircMessage->getNickname());

		if ($userObject === UserCollection::fromContainer($this->getContainer())->getSelf())
			$channel->abandon();

		$removed = $channel->getUserCollection()
			->remove(function (User $user) use ($userObject)
			{
				return $user === $userObject;
			});

		$removedChannel = $userObject->getChannelCollection()
			->remove(function (Channel $channelObject) use ($channel)
			{
				return $channelObject === $channel;
			});

		if ($removed && $removedChannel)
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
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($ircMessage->getChannel());
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($ircMessage->getTarget());

		$removed = $channel->getUserCollection()
			->remove(function (User $user) use ($userObject)
			{
				return $user === $userObject;
			});

		$removedChannel = $userObject->getChannelCollection()
			->remove(function (Channel $channelObject) use ($channel)
			{
				return $channelObject === $channel;
			});

		if ($removed && $removedChannel)
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
	 * @param Queue $queue
	 */
	public function processUserQuit(User $userObject, Queue $queue)
	{
		$channels = ChannelCollection::fromContainer($this->getContainer())
			->toArray();

		foreach ($channels as $channel)
		{
			$userCollection = $channel->getUserCollection();

			$removed = $userCollection->remove(function (User $user) use ($userObject, $channel)
			{

				if ($user === $userObject)
				{
					$user->getChannelCollection()
						->remove(function (Channel $channelObject) use ($channel)
						{
							return $channelObject === $channel;
						});

					return true;
				}

				return false;
			});

			if ($removed)
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
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($channel);

		foreach ($modes as $mode)
		{
			if ($shouldBeRemoved)
				$channel->getChannelModes()
					->UserFromMode($mode, $target);
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
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function populateChannel(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$channel = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($args[2]);
		$nicknames = explode(' ', $args[3]);

		foreach ($nicknames as $nicknameWithMode)
		{
			$nickname = $nicknameWithMode;
			$modes = $channel->getChannelModes()
				->extractUserModesFromNickname($nicknameWithMode, $nickname);
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
					->add($channel);

			if (!$channel->getUserCollection()
				->findByNickname($userObject->getNickname())
			)
				$channel->getUserCollection()
					->add($userObject);

			Logger::fromContainer($this->getContainer())->debug('Added user to channel', [
				'reason' => 'initialJoin',
				'nickname' => $userObject->getNickname(),
				'channel' => $channel->getName()
			]);
		}
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processChannelTopicChange(IncomingIrcMessage $ircMessage, Queue $queue)
	{

	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public function joinInitialChannels(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$channels = Configuration::fromContainer($this->getContainer())
			->get('channels')
			->getValue();

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