<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Events\OutgoingIrcMessageEvent;
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
        $eventEmitter->on('irc.msg.out.privmsg', [$this, 'logOutgoingPrivmsg']);

        $this->logger = $logger;
    }

    /**
     * @param PRIVMSG $incoming
     */
    public function logIncomingPrivmsg(PRIVMSG $incoming): void
    {
        $nickname = $incoming->getNickname();
        $channel = $incoming->getChannel();
        $message = $incoming->getMessage();

        $this->logger->info('INC: [' . $channel . '] <' . $nickname . '> ' . $message);
    }

    /**
     * @param OutgoingIrcMessageEvent $event
     */
    public function logOutgoingPrivmsg(OutgoingIrcMessageEvent $event): void
    {
        $command = $event->getOutgoingMessage();

        if (!($command instanceof PRIVMSG)) {
            return;
        }

        $channel = $command->getChannel();
        $msg = $command->getMessage();

        $this->logger->info('OUT: [' . $channel . '] ' . $msg);
    }
}
