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
use WildPHP\Core\Events\NicknameChangedEvent;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Nick;

class NickObserver
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
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * NickObserver constructor.
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcUserStorageInterface $userStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcUserStorageInterface $userStorage
    ) {
        $eventEmitter->on('irc.msg.in.nick', [$this, 'processUserNickChange']);

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->userStorage = $userStorage;
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserNickChange(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Nick $nickMessage */
        $nickMessage = $ircMessageEvent->getIncomingMessage();

        $user = $this->userStorage->getOneByNickname($nickMessage->getNickname());

        if ($user === null) {
            throw new RuntimeException('No user found while one was expected');
        }

        $user->setNickname($nickMessage->getNewNickname());
        $this->userStorage->store($user);

        $this->eventEmitter->emit('user.nick', [
            new NicknameChangedEvent(
                $user,
                $nickMessage->getNickname(),
                $nickMessage->getNewNickname()
            )
        ]);

        $this->logger->debug('Updated user nickname', [
            'oldNickname' => $nickMessage->getNickname(),
            'nickname' => $nickMessage->getNewNickname()
        ]);
    }
}
