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
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\RPL\WhosPcRpl;

class WhosPcRplObserver
{
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
     * @param IrcUserStorageInterface $userStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcUserStorageInterface $userStorage
    ) {
        // 354: RPL_WHOSPCRPL
        $eventEmitter->on('irc.msg.in.354', [$this, 'processWhosPcRplReply']);

        $this->logger = $logger;
        $this->userStorage = $userStorage;
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processWhosPcRplReply(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var WhosPcRpl $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();

        $user = $this->userStorage->getOrCreateOneByNickname($ircMessage->getNickname());
        $user->nickname = $ircMessage->getNickname();
        $user->username = $ircMessage->getUsername();
        $user->hostname = $ircMessage->getHostname();
        $user->ircAccount = $ircMessage->getAccountname();
        $this->userStorage->store($user);

        $this->logger->debug(
            'Updated user details',
            array_merge(['reason' => 'rpl_whospcrpl'], $user->toArray())
        );
    }
}
