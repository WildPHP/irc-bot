<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Join;
use WildPHP\Messages\RPL\NamReply;
use WildPHP\Messages\RPL\Topic;

class ChannelObserver
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

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
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     * @param IrcMessageQueue $queue
     * @param IrcUserStorageInterface $userStorage
     * @param IrcChannelStorageInterface $channelStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        LoggerInterface $logger,
        IrcMessageQueue $queue,
        IrcUserStorageInterface $userStorage,
        IrcChannelStorageInterface $channelStorage
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

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->queue = $queue;
        $this->configuration = $configuration;
        $this->userStorage = $userStorage;
        $this->channelStorage = $channelStorage;
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

        $this->logger->debug('Queued initial channel join.',
            [
                'count' => count($channels),
                'channels' => $channels
            ]);
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function createChannel(IncomingIrcMessageEvent $ircMessageEvent)
    {
        /** @var Join $joinMessage */
        $joinMessage = $ircMessageEvent->getIncomingMessage();

        foreach ($joinMessage->getChannels() as $channelName) {
            if ($this->channelStorage->getOneByName($channelName) == null) {
                $channel = new IrcChannel($channelName);
                $this->channelStorage->store($channel);

                $this->logger->debug('Created new channel', [
                    'id' => $channel->getId(),
                    'name' => $channel->getName()
                ]);
            }
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processChannelJoin(IncomingIrcMessageEvent $ircMessageEvent)
    {
        // TODO: REIMPLEMENT THIS
        /*$user = IrcUserQuery::create()->findOneByNickname($joinMessage->getNickname());

        foreach ($joinMessage->getChannels() as $channelName) {
            $channel = IrcChannelQuery::create()->findOneByName($channelName);
            $channel->addIrcUser($user);
            $channel->save();

            $this->logger->debug('Creating user-channel relationship', [
                'reason' => 'join',
                'userID' => $user->getId(),
                'nickname' => $user->getNickname(),
                'channelID' => $channel->getId(),
                'channel' => $channel->getName()
            ]);
        }*/
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processTopic(IncomingIrcMessageEvent $ircMessageEvent)
    {
        /** @var Topic $topicMessage */
        $topicMessage = $ircMessageEvent->getIncomingMessage();

        $channel = $this->channelStorage->getOneByName($topicMessage->getChannel());
        $channel->setTopic($topicMessage->getMessage());
        $this->channelStorage->store($channel);

        $this->logger->debug('Updated topic', [
            'channel' => $topicMessage->getChannel(),
            'topic' => $topicMessage->getMessage()
        ]);
    }

    /**
     * @param NamReply $ircMessage
     */
    public function processNamesReply(NamReply $ircMessage)
    {
        // TODO: REIMPLEMENT THIS
        /*$nicknames = $ircMessage->getNicknames();

        $channel = IrcChannelQuery::create()->findOneByName($ircMessage->getChannel());

        foreach ($nicknames as $nicknameWithMode) {
            $nickname = '';
            UserModeParser::extractFromNickname($nicknameWithMode, $nickname);

            $user = IrcUserQuery::create()->findOneByNickname($nickname);

            if ($channel->getIrcUsers()->contains($user)) {
                return;
            }

            $channel->addIrcUser($user);

            $this->logger->debug('Creating user-channel relationship', [
                'reason' => 'rpl_namreply',
                'userID' => $user->getId(),
                'nickname' => $user->getNickname(),
                'channelID' => $channel->getId(),
                'channel' => $channel->getName()
            ]);
        }

        $channel->save();*/
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserKick(IncomingIrcMessageEvent $ircMessageEvent)
    {
        // TODO: REIMPLEMENT THIS
        /*
        $user = IrcUserQuery::create()->findOneByNickname($kickMessage->getNickname());
        $channel = IrcChannelQuery::create()->findOneByName($kickMessage->getChannel());
        $channel->removeIrcUser($user);
        $channel->save();

        $this->logger->debug('Removed user-channel relationship', [
            'reason' => 'kick',
            'nickname' => $kickMessage->getNickname(),
            'channel' => $kickMessage->getChannel()
        ]);
        */
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserPart(IncomingIrcMessageEvent $ircMessageEvent)
    {
        // TODO: REIMPLEMENT THIS
        /*
        $user = IrcUserQuery::create()->findOneByNickname($partMessage->getNickname());

        foreach ($partMessage->getChannels() as $channel) {
            $channel = IrcChannelQuery::create()->findOneByName($channel);
            $channel->removeIrcUser($user);
            $channel->save();

            $this->logger->debug('Removed user-channel relationship', [
                'reason' => 'part',
                'nickname' => $partMessage->getNickname(),
                'channel' => $channel
            ]);
        }
        */
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserQuit(IncomingIrcMessageEvent $ircMessageEvent)
    {
        // TODO: REIMPLEMENT THIS
        /*
        $user = IrcUserQuery::create()->findOneByNickname($quitMessage->getNickname());
        $user->getIrcChannels()->clear();
        $user->save();

        $this->logger->debug('Cleared all user-channel relationships', [
            'reason' => 'quit',
            'nickname' => $quitMessage->getNickname()
        ]);
        */
    }
}
