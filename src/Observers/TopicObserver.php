<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Messages\RPL\Topic;

class TopicObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * TopicObserver constructor.
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcChannelStorageInterface $channelStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcChannelStorageInterface $channelStorage
    ) {
        // 332: RPL_TOPIC
        $eventEmitter->on('irc.line.in.332', [$this, 'processTopic']);

        $this->logger = $logger;
        $this->channelStorage = $channelStorage;
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

        $channel->topic = $topicMessage->getMessage();
        $this->channelStorage->store($channel);

        $this->logger->debug('Updated topic', [
            'channel' => $topicMessage->getChannel(),
            'topic' => $topicMessage->getMessage()
        ]);
    }
}
