<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Messages\RPL\EndOfNames;

class EndOfNamesObserver
{
    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param IrcMessageQueue $queue
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        IrcMessageQueue $queue
    ) {
        // 366: RPL_ENDOFNAMES
        $eventEmitter->on('irc.msg.in.366', [$this, 'sendInitialWhoxMessage']);

        $this->queue = $queue;
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function sendInitialWhoxMessage(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var EndOfNames $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();

        $channel = $ircMessage->getChannel();
        $this->queue->who($channel, '%nuhaf');
    }
}
