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
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Join;

class JoinObserver
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
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * @var IrcUserChannelRelationStorageInterface
     */
    private $relationStorage;

    /**
     * JoinObserver constructor.
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcChannelStorageInterface $channelStorage
     * @param IrcUserStorageInterface $userStorage
     * @param IrcUserChannelRelationStorageInterface $relationStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcChannelStorageInterface $channelStorage,
        IrcUserStorageInterface $userStorage,
        IrcUserChannelRelationStorageInterface $relationStorage
    ) {
        $eventEmitter->on('irc.msg.in.join', [$this, 'processChannelJoin']);
        $eventEmitter->on('irc.msg.in.join', [$this, 'processUserJoin']);

        $this->logger = $logger;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->relationStorage = $relationStorage;
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
            $channelObject = $this->channelStorage->getOrCreateOneByName($channel);

            $relation = $this->relationStorage->getOrCreateOne(
                $user->userId,
                $channelObject->channelId
            );

            $this->logger->debug('Creating user-channel relationship', [
                'reason' => 'join',
                'userID' => $relation->ircUserId,
                'nickname' => $user->nickname,
                'channelID' => $relation->ircChannelId,
                'channel' => $channel
            ]);
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserJoin(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Join $joinMessage */
        $joinMessage = $ircMessageEvent->getIncomingMessage();

        $user = $this->userStorage->getOrCreateOneByNickname($joinMessage->getNickname());

        $prefix = $joinMessage->getPrefix();
        $user->username = $prefix->getUsername();
        $user->hostname = $prefix->getHostname();
        $user->ircAccount = $joinMessage->getIrcAccount();
        $user->online = true;

        $this->logger->debug('Updated user', [
            'reason' => 'join',
            'id' => $user->userId,
            'nickname' => $joinMessage->getNickname(),
            'username' => $prefix->getUsername(),
            'hostname' => $prefix->getHostname(),
            'irc_account' => $joinMessage->getIrcAccount()
        ]);

        $this->userStorage->store($user);

        $this->logger->debug('Set online flag for user', [
            'reason' => 'join',
            'id' => $user->userId,
            'nickname' => $user->nickname,
            'newValue' => $user->online
        ]);
    }
}
