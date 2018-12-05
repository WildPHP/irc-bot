<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Propel\Runtime\Exception\PropelException;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\UserModeParser;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcChannelQuery;
use WildPHP\Core\Entities\IrcUserQuery;
use WildPHP\Messages\Join;
use WildPHP\Messages\Kick;
use WildPHP\Messages\Part;
use WildPHP\Messages\Quit;
use WildPHP\Messages\RPL\NamReply;
use WildPHP\Messages\RPL\Topic;
use WildPHP\Core\Connection\QueueInterface;

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
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     * @param QueueInterface $queue
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        LoggerInterface $logger,
        QueueInterface $queue
    ) {
        $eventEmitter->on('irc.line.in.join', [$this, 'createChannel']);
        $eventEmitter->on('irc.line.in.join', [$this, 'processChannelJoin']);
        $eventEmitter->on('irc.line.in.kick', [$this, 'processUserKick']);
        $eventEmitter->on('irc.line.in.part', [$this, 'processUserPart']);
        $eventEmitter->on('irc.line.in.quit', [$this, 'processUserQuit']);

        // 001: RPL_WELCOME
        $eventEmitter->on('irc.line.in.001', [$this, 'joinInitialChannels']);

        // 332: RPL_TOPIC
        $eventEmitter->on('irc.line.in.332', [$this, 'processTopic']);

        // 353: RPL_NAMREPLY
        $eventEmitter->on('irc.line.in.353', [$this, 'processNamesReply']);

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->queue = $queue;
        $this->configuration = $configuration;
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     */
    public function joinInitialChannels()
    {
        $channels = $this->configuration['channels'];

        if (empty($channels)) {
            return;
        }

        $chunks = array_chunk($channels, 3);
        $this->queue->setFloodControl(true);

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
     * @param JOIN $joinMessage
     * @throws PropelException
     */
    public function createChannel(JOIN $joinMessage)
    {
        foreach ($joinMessage->getChannels() as $channelName) {
            if (IrcChannelQuery::create()->findOneByName($channelName) == null) {
                $channel = new IrcChannel();
                $channel->setName($channelName);
                $channel->save();

                $this->logger->debug('Created new channel', [
                    'name' => $channel->getName()
                ]);
            }
        }
    }

    /**
     * @param JOIN $joinMessage
     * @throws PropelException
     */
    public function processChannelJoin(JOIN $joinMessage)
    {
        $user = IrcUserQuery::create()->findOneByNickname($joinMessage->getNickname());

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
        }
    }

    /**
     * @param Topic $topicMessage
     * @throws PropelException
     */
    public function processTopic(Topic $topicMessage)
    {
        $channel = IrcChannelQuery::create()->findOneByName($topicMessage->getChannel());
        $channel->setTopic($topicMessage->getMessage());
        $channel->save();

        $this->logger->debug('Updated topic', [
            'channel' => $topicMessage->getChannel(),
            'topic' => $topicMessage->getMessage()
        ]);
    }

    /**
     * @param NamReply $ircMessage
     * @throws PropelException
     */
    public function processNamesReply(NamReply $ircMessage)
    {
        $nicknames = $ircMessage->getNicknames();

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

        $channel->save();
    }

    /**
     * @param KICK $kickMessage
     * @throws PropelException
     */
    public function processUserKick(KICK $kickMessage)
    {
        $user = IrcUserQuery::create()->findOneByNickname($kickMessage->getNickname());
        $channel = IrcChannelQuery::create()->findOneByName($kickMessage->getChannel());
        $channel->removeIrcUser($user);
        $channel->save();

        $this->logger->debug('Removed user-channel relationship', [
            'reason' => 'kick',
            'nickname' => $kickMessage->getNickname(),
            'channel' => $kickMessage->getChannel()
        ]);
    }

    /**
     * @param PART $partMessage
     * @throws PropelException
     */
    public function processUserPart(PART $partMessage)
    {
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
    }

    /**
     * @param QUIT $quitMessage
     * @throws PropelException
     */
    public function processUserQuit(QUIT $quitMessage)
    {
        $user = IrcUserQuery::create()->findOneByNickname($quitMessage->getNickname());
        $user->getIrcChannels()->clear();
        $user->save();

        $this->logger->debug('Cleared all user-channel relationships', [
            'reason' => 'quit',
            'nickname' => $quitMessage->getNickname()
        ]);
    }
}
