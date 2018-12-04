<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Messages\Privmsg;

class MessageLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger)
    {
        $eventEmitter->on('irc.line.in.privmsg', [$this, 'logIncomingPrivmsg']);
        $eventEmitter->on('irc.line.out', [$this, 'logOutgoingPrivmsg']);

        $this->logger = $logger;
    }

    /**
     * @param PRIVMSG $incoming
     */
    public function logIncomingPrivmsg(PRIVMSG $incoming)
    {
        $nickname = $incoming->getNickname();
        $channel = $incoming->getChannel();
        $message = $incoming->getMessage();

        $this->logger->info('INC: [' . $channel . '] <' . $nickname . '> ' . $message);
    }

    /**
     * @param QueueItem $message
     */
    public function logOutgoingPrivmsg(QueueItem $message)
    {
        $command = $message->getCommandObject();

        if (!($command instanceof PRIVMSG)) {
            return;
        }

        $channel = $command->getChannel();
        $msg = $command->getMessage();

        $this->logger->info('OUT: [' . $channel . '] ' . $msg);
    }
}