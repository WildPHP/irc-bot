<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;


use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Database\Database;
use WildPHP\Core\Users\User;
use WildPHP\Messages\Account;

class AccountNotifyHandler
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
     * @var Database
     */
    private $database;

    /**
     * AccountNotifyHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param Database $database
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger, Database $database)
    {
        $eventEmitter->on('irc.line.in.account', [$this, 'updateUserIrcAccount']);
        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->database = $database;
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param ACCOUNT $ircMessage
     * @throws \WildPHP\Core\StateException
     * @throws \WildPHP\Core\Users\UserNotFoundException
     */
    public function updateUserIrcAccount(ACCOUNT $ircMessage)
    {
        $nickname = $ircMessage->getPrefix()->getNickname();
        $db = $this->database;

        $user = User::fromDatabase($db, ['nickname' => $nickname]);
        $this->logger->debug('Updated irc account for userid ' . $user->getId());
        $user->setIrcAccount($ircMessage->getAccountName());

        User::toDatabase($db, $user);
    }
}