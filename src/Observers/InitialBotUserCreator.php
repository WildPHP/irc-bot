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
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\RPL\Welcome;

class InitialBotUserCreator
{
    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InitialBotUserCreator constructor.
     * @param IrcUserStorageInterface $userStorage
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(
        IrcUserStorageInterface $userStorage,
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger
    ) {
        $eventEmitter->on('irc.msg.in.001', [$this, 'createInitialBotUser']);
        $this->userStorage = $userStorage;
        $this->logger = $logger;
    }

    /**
     * @param IncomingIrcMessageEvent $event
     */
    public function createInitialBotUser(IncomingIrcMessageEvent $event): void
    {
        /** @var Welcome $message */
        $message = $event->getIncomingMessage();
        $nickname = $message->getNickname();
        $user = $this->userStorage->getOrCreateOneByNickname($nickname);
        $this->logger->debug('Created initial user; my job is done', [
            'id' => $user->id,
            'nickname' => $nickname
        ]);
    }
}
