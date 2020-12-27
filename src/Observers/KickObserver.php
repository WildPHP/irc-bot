<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Kick;

class KickObserver
{
    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * @var IrcUserChannelRelationStorageInterface
     */
    private $relationStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * BaseModule constructor.
     *
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
        $eventEmitter->on('irc.msg.in.kick', [$this, 'processUserKick']);

        $this->logger = $logger;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->relationStorage = $relationStorage;
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

        $this->relationStorage->delete(
            $this->relationStorage->getOne($user->id, $channel->id)
        );

        $this->logger->debug('Removed user-channel relationship', [
            'reason' => 'kick',
            'nickname' => $kickMessage->getNickname(),
            'channel' => $kickMessage->getChannel()
        ]);
    }
}
