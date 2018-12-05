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
use WildPHP\Core\Entities\Base\IrcUserQuery;
use WildPHP\Messages\Account;

class AccountNotifyHandler
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AccountNotifyHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger)
    {
        $eventEmitter->on('irc.line.in.account', [$this, 'updateUserIrcAccount']);
        $this->logger = $logger;
    }

    /**
     * @param ACCOUNT $ircMessage
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateUserIrcAccount(ACCOUNT $ircMessage)
    {
        $nickname = $ircMessage->getPrefix()->getNickname();
        $user = IrcUserQuery::create()->findOneByNickname($nickname);
        $user->setIrcAccount($ircMessage->getAccountName());
        $user->save();

        $this->logger->debug('Updated IRC account', [
            'reason' => 'account_notify',
            'userID' => $user->getId(),
            'nickname' => $user->getNickname(),
            'new_ircAccount' => $user->getIrcAccount()
        ]);
    }
}