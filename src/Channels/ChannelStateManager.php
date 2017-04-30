<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 26-4-2017
 * Time: 16:11
 */

namespace WildPHP\Core\Channels;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\UserPrefix;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class ChannelStateManager
{
	/**
	 * @var ComponentContainer
	 */
	protected $container = null;

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
			$container->getEventEmitter()->on($event, [$this, $callback]);
		}

		$this->setContainer($container);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processUserJoin(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$prefix = UserPrefix::fromIncomingIrcMessage($ircMessage);
		$userObject = $this->getContainer()->getUserCollection()->findOrCreateByNickname($prefix->getNickname());
		$args = $ircMessage->getArgs();
		$channel = $this->getContainer()->getChannelCollection()->findByChannelName($args[0]);
		$accountname = $args[1];

		if ($channel == false)
		{
			$userCollection = new UserCollection($this->getContainer());
			$channelmodes = new ChannelModes($this->getContainer());
			$channel = new Channel($userCollection, $channelmodes);
			$channel->setName($args[0]);
			$this->getContainer()->getChannelCollection()->add($channel);
		}

		// TODO Isn't this really UserStateManager's job?
		$userObject->setIrcAccount($accountname);
		$userObject->setHostname($prefix->getHostname());
		$userObject->setUsername($prefix->getUsername());
		$userObject->getChannelCollection()->add($channel);

		$channel->getUserCollection()->add($userObject);

		$this->getContainer()->getEventEmitter()->emit('user.join', [$userObject, $channel, $queue]);

		$this->getContainer()->getLogger()->debug('Added user to channel.', [
			'reason' => 'join',
			'nickname' => $userObject->getNickname(),
			'channel' => $channel->getName()
		]);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processUserPart(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$userPrefix = UserPrefix::fromIncomingIrcMessage($ircMessage);
		$channel = $this->getContainer()->getChannelCollection()->findByChannelName($args[0]);
		$userObject = $this->getContainer()->getUserCollection()->findByNickname($userPrefix->getNickname());

		if ($userObject === $this->getContainer()->getUserCollection()->getSelf())
			$channel->abandon();

		$removed = $channel->getUserCollection()->remove(function (User $user) use ($userObject)
		{
			return $user === $userObject;
		});

		$removedChannel = $userObject->getChannelCollection()->remove(function (Channel $channelObject) use ($channel)
		{
			return $channelObject === $channel;
		});

		if ($removed && $removedChannel)
			$this->getContainer()->getLogger()->debug('Removed user from channel', [
				'reason' => 'part',
				'nickname' => $userObject->getNickname(),
				'channel' => $channel->getName()
			]);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processUserKick(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$channel = $this->getContainer()->getChannelCollection()->findByChannelName($args[0]);
		$userObject = $this->getContainer()->getUserCollection()->findByNickname($args[1]);

		$removed = $channel->getUserCollection()->remove(function (User $user) use ($userObject)
		{
			return $user === $userObject;
		});

		$removedChannel = $userObject->getChannelCollection()->remove(function (Channel $channelObject) use ($channel)
		{
			return $channelObject === $channel;
		});

		if ($removed && $removedChannel)
			$this->getContainer()->getLogger()->debug('Removed user from channel', [
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
		$channels = $this->getContainer()->getChannelCollection()->toArray();

		foreach ($channels as $channel)
		{
			$userCollection = $channel->getUserCollection();

			$removed = $userCollection->remove(function (User $user) use ($userObject, $channel)
			{

				if ($user === $userObject)
				{
					$user->getChannelCollection()->remove(function (Channel $channelObject) use ($channel)
					{
						return $channelObject === $channel;
					});
					return true;
				}
				return false;
			});

			if ($removed)
				$this->getContainer()->getLogger()->debug('Removed user from channel', [
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
		$channel = $this->getContainer()->getChannelCollection()->findByChannelName($channel);

		foreach ($modes as $mode)
		{
			if ($shouldBeRemoved)
				$channel->getChannelModes()->UserFromMode($mode, $target);
			else
				$channel->getChannelModes()->addUserToMode($mode, $target);
		}

		$this->getContainer()->getLogger()->debug('Updated mode for user', [
			'channel' => $channel->getName(),
			'nickname' => $target->getNickname(),
			'diff' => $modes,
			'newmodes' => $channel->getChannelModes()->getModesForUser($target)
		]);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function populateChannel(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$channel = $this->getContainer()->getChannelCollection()->findByChannelName($args[2]);
		$nicknames = explode(' ', $args[3]);

		foreach ($nicknames as $nicknameWithMode)
		{
			$nickname = $nicknameWithMode;
			$modes = $channel->getChannelModes()->extractUserModesFromNickname($nicknameWithMode, $nickname);
			$userObject = $this->getContainer()->getUserCollection()->findOrCreateByNickname($nickname);

			if (!empty($modes))
			{
				foreach ($modes as $mode)
					$channel->getChannelModes()->addUserToMode($mode, $userObject);
			}

			if (!$userObject->getChannelCollection()->findByChannelName($channel->getName()))
				$userObject->getChannelCollection()->add($channel);

			if (!$channel->getUserCollection()->findByNickname($userObject->getNickname()))
				$channel->getUserCollection()->add($userObject);
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
		$channels = $this->getContainer()->getConfiguration()->get('channels')->getValue();

		if (empty($channels))
			return;

		$chunks = array_chunk($channels, 3);
		$queue->setFloodControl(true);

		foreach ($chunks as $chunk)
		{
			$queue->join($chunk);
		}

		$this->getContainer()->getLogger()->debug('Queued initial channel join.', [
			'count' => count($channels),
			'channels' => $channels
		]);
	}

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}
}