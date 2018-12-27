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
use React\Promise\PromiseInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Account;

class AccountNotifyHandler extends RequestOnlyHandler implements CapabilityInterface
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
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * AccountNotifyHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcUserStorageInterface $userStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcUserStorageInterface $userStorage
    )
    {
        $this->logger = $logger;
        $this->userStorage = $userStorage;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @param ACCOUNT $ircMessage
     */
    public function updateUserIrcAccount(ACCOUNT $ircMessage)
    {
        $nickname = $ircMessage->getPrefix()->getNickname();
        $user = $this->userStorage->getOneByNickname($nickname);
        $user->setIrcAccount($ircMessage->getAccountName());
        $this->userStorage->store($user);

        $this->logger->debug('Updated IRC account', [
            'reason' => 'account_notify',
            'userID' => $user->getId(),
            'nickname' => $user->getNickname(),
            'new_ircAccount' => $user->getIrcAccount()
        ]);
    }

    /**
     * @param PromiseInterface $promise
     * @return void
     */
    public function setRequestPromise(PromiseInterface $promise)
    {
        parent::setRequestPromise($promise);
        $promise->then(function () {
            $this->eventEmitter->on('irc.line.in.account', [$this, 'updateUserIrcAccount']);
        });
    }
}