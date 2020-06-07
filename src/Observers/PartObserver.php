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
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Part;

class PartObserver
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
        $eventEmitter->on('irc.msg.in.part', [$this, 'processUserPart']);

        $this->logger = $logger;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->relationStorage = $relationStorage;
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

            $this->relationStorage->delete(
                $this->relationStorage->getOne($user->userId, $channel->channelId)
            );

            $this->logger->debug('Removed user-channel relationship', [
                'reason' => 'part',
                'nickname' => $partMessage->getNickname(),
                'channel' => $channelName
            ]);
        }

        if (empty($this->relationStorage->getByUserId($user->userId))) {
            $user->online = false;
            $this->userStorage->store($user);

            $this->logger->debug('This user has left all mutual channels; assuming offline. Updated online flag', [
                'reason' => 'part',
                'id' => $user->userId,
                'nickname' => $user->nickname,
                'newValue' => $user->online
            ]);
        }
    }
}
