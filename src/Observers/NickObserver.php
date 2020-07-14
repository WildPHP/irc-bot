<?php
/**
 * Copyright 2020 The WildPHP Team
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
        $existingUser = $this->userStorage->getOneByNickname($nickMessage->getNewNickname());

        if ($user === null) {
            throw new RuntimeException('No user found while one was expected');
        }

        if ($existingUser !== null) {
            $this->logger->debug('Found existing user with the same nickname; dropping duplicate user.');
            $this->userStorage->delete($existingUser);
        }

        $user->nickname = $nickMessage->getNewNickname();
        $this->userStorage->store($user);

        $this->logger->debug('Updated user nickname', [
            'oldNickname' => $nickMessage->getNickname(),
            'nickname' => $nickMessage->getNewNickname()
        ]);

        $this->eventEmitter->emit('user.nick', [
            new NicknameChangedEvent(
                $user,
                $nickMessage->getNickname(),
                $nickMessage->getNewNickname()
            )
        ]);
    }
}
