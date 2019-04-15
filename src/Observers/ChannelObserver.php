<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\UserModeParser;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Join;
use WildPHP\Messages\Kick;
use WildPHP\Messages\Part;
use WildPHP\Messages\Quit;
use WildPHP\Messages\RPL\NamReply;
use WildPHP\Messages\RPL\Topic;

class ChannelObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;
    /**
     * @var IrcUserChannelRelationStorageInterface
     */
    private $userChannelRelationStorage;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     * @param IrcMessageQueue $queue
     * @param IrcChannelStorageInterface $channelStorage
     * @param IrcUserStorageInterface $userStorage
     * @param IrcUserChannelRelationStorageInterface $userChannelRelationStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        LoggerInterface $logger,
        IrcMessageQueue $queue,
        IrcChannelStorageInterface $channelStorage,
        IrcUserStorageInterface $userStorage,
        IrcUserChannelRelationStorageInterface $userChannelRelationStorage
    ) {
        $eventEmitter->on('irc.msg.in.join', [$this, 'createChannel']);
        $eventEmitter->on('irc.msg.in.join', [$this, 'processChannelJoin']);
        $eventEmitter->on('irc.msg.in.kick', [$this, 'processUserKick']);
        $eventEmitter->on('irc.msg.in.part', [$this, 'processUserPart']);
        $eventEmitter->on('irc.msg.in.quit', [$this, 'processUserQuit']);

        // 001: RPL_WELCOME
        $eventEmitter->on('irc.msg.in.001', [$this, 'joinInitialChannels']);

        // 332: RPL_TOPIC
        $eventEmitter->on('irc.line.in.332', [$this, 'processTopic']);

        // 353: RPL_NAMREPLY
        $eventEmitter->on('irc.line.in.353', [$this, 'processNamesReply']);

        $this->logger = $logger;
        $this->queue = $queue;
        $this->configuration = $configuration;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->userChannelRelationStorage = $userChannelRelationStorage;
    }

    /**
     * @return void
     */
    public function joinInitialChannels(): void
    {
        $channels = $this->configuration['connection']['channels'];

        if (empty($channels)) {
            return;
        }

        $chunks = array_chunk($channels, 3);

        foreach ($chunks as $chunk) {
            $this->queue->join($chunk);
        }

        $this->logger->debug(
            'Queued initial channel join.',
            [
                'count' => count($channels),
                'channels' => $channels
            ]
        );
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function createChannel(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Join $joinMessage */
        $joinMessage = $ircMessageEvent->getIncomingMessage();

        foreach ($joinMessage->getChannels() as $channelName) {
            if ($this->channelStorage->getOneByName($channelName) === null) {
                $channel = new IrcChannel($channelName);
                $this->channelStorage->store($channel);

                $this->logger->debug('Created new channel', [
                    'id' => $channel->getChannelId(),
                    'name' => $channel->getName()
                ]);
            }
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processChannelJoin(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Join $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();
        $user = $this->userStorage->getOrCreateOneByNickname($ircMessage->getNickname());
        $channels = $ircMessage->getChannels();

        foreach ($channels as $channel) {
            /** @var IrcChannel $channelObject */
            $channelObject = $this->channelStorage->getOneByName($channel);

            $relation = $this->userChannelRelationStorage->getOrCreateOne(
                $user->getUserId(),
                $channelObject->getChannelId()
            );

            $this->logger->debug('Creating user-channel relationship', [
                'reason' => 'join',
                'userID' => $relation->getIrcUserId(),
                'nickname' => $user->getNickname(),
                'channelID' => $relation->getIrcChannelId(),
                'channel' => $channel
            ]);
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processTopic(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Topic $topicMessage */
        $topicMessage = $ircMessageEvent->getIncomingMessage();

        $channel = $this->channelStorage->getOneByName($topicMessage->getChannel());

        if ($channel === null) {
            throw new RuntimeException('No channel found while one was expected');
        }

        $channel->setTopic($topicMessage->getMessage());
        $this->channelStorage->store($channel);

        $this->logger->debug('Updated topic', [
            'channel' => $topicMessage->getChannel(),
            'topic' => $topicMessage->getMessage()
        ]);
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processNamesReply(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var NamReply $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();
        $nicknames = $ircMessage->getNicknames();

        $channel = $this->channelStorage->getOrCreateOneByName($ircMessage->getChannel());

        foreach ($nicknames as $nicknameWithMode) {
            $nickname = '';
            $modes = UserModeParser::extractFromNickname($nicknameWithMode, $nickname);
            $user = $this->userStorage->getOrCreateOneByNickname($nickname);

            $relation = $this->userChannelRelationStorage->getOrCreateOne(
                $user->getUserId(),
                $channel->getChannelId(),
                $modes
            );

            $this->logger->debug('Creating user-channel relationship', [
                'reason' => 'rpl_namreply',
                'userID' => $relation->getIrcUserId(),
                'nickname' => $user->getNickname(),
                'channelID' => $relation->getIrcChannelId(),
                'channel' => $channel->getName()
            ]);
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserKick(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Kick $kickMessage */
        $kickMessage = $ircMessageEvent->getIncomingMessage();

        /** @var IrcUser $user */
        $user = $this->userStorage->getOneByNickname($kickMessage->getNickname());
        /** @var IrcChannel $channel */
        $channel = $this->channelStorage->getOneByName($kickMessage->getChannel());

        $this->userChannelRelationStorage->delete(
            $this->userChannelRelationStorage->getOne($user->getUserId(), $channel->getChannelId())
        );

        $this->logger->debug('Removed user-channel relationship', [
            'reason' => 'kick',
            'nickname' => $kickMessage->getNickname(),
            'channel' => $kickMessage->getChannel()
        ]);
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserPart(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Part $partMessage */
        $partMessage = $ircMessageEvent->getIncomingMessage();

        /** @var IrcUser $user */
        $user = $this->userStorage->getOneByNickname($partMessage->getNickname());

        foreach ($partMessage->getChannels() as $channelName) {
            /** @var IrcChannel $channel */
            $channel = $this->channelStorage->getOneByName($channelName);

            $this->userChannelRelationStorage->delete(
                $this->userChannelRelationStorage->getOne($user->getUserId(), $channel->getChannelId())
            );

            $this->logger->debug('Removed user-channel relationship', [
                'reason' => 'part',
                'nickname' => $partMessage->getNickname(),
                'channel' => $channelName
            ]);
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserQuit(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Quit $quitMessage */
        $quitMessage = $ircMessageEvent->getIncomingMessage();

        /** @var IrcUser $user */
        $user = $this->userStorage->getOneByNickname($quitMessage->getNickname());

        foreach ($this->userChannelRelationStorage->getByUserId($user->getUserId()) as $relation) {
            $this->userChannelRelationStorage->delete($relation);
        }

        $this->logger->debug('Removed all user-channel relationships', [
            'reason' => 'quit',
            'nickname' => $quitMessage->getNickname()
        ]);
    }
}
