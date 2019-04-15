<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Queue;

use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;
use WildPHP\Core\Connection\IrcConnectionInterface;
use WildPHP\Core\Events\OutgoingIrcMessageEvent;

class QueueProcessor
{
    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var int
     */
    private $burstAmount = 5;

    /**
     * @var int
     */
    private $burstTrigger = 3;

    /**
     * @var bool
     */
    private $usedBurst = false;

    /**
     * @var int
     */
    private $messagesPerSecondAfterBurst = 1;

    /**
     * @var IrcConnectionInterface
     */
    private $ircConnection;
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * QueueProcessor constructor.
     * @param IrcMessageQueue $queue
     * @param LoopInterface $loop
     * @param IrcConnectionInterface $ircConnection
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        IrcMessageQueue $queue,
        LoopInterface $loop,
        IrcConnectionInterface $ircConnection,
        EventEmitterInterface $eventEmitter
    ) {
        $loop->addPeriodicTimer(1, [$this, 'processDueItems']);
        $this->queue = $queue;
        $this->ircConnection = $ircConnection;
        $this->eventEmitter = $eventEmitter;
    }

    public function processDueItems(): void
    {
        /** @var QueueItemInterface[] $items */
        $items = $this->queue->toArray();
        $amountToProcess = $this->messagesPerSecondAfterBurst;

        // Use up our burst if we haven't used it and
        /** @noinspection NotOptimalIfConditionsInspection */
        if (count($items) > $this->burstTrigger && !$this->usedBurst) {
            $amountToProcess = $this->burstAmount;
            $this->usedBurst = true;
        }

        $itemsToProcess = array_slice($items, 0, $amountToProcess);

        // Reset the burst flag when the queue is now empty
        /** @noinspection NotOptimalIfConditionsInspection */
        if (count($items) === 0 && $this->usedBurst) {
            $this->usedBurst = false;
        }

        if (empty($itemsToProcess)) {
            return;
        }

        foreach ($itemsToProcess as $queueItem) {
            $this->queue->dequeue($queueItem);
            $outgoingMessage = $queueItem->getOutgoingMessage();
            $this->ircConnection->write($outgoingMessage->__toString());

            $event = new OutgoingIrcMessageEvent($outgoingMessage);
            $this->eventEmitter->emit('irc.msg.out', [$event]);
            $this->eventEmitter->emit('irc.msg.out.' . strtolower($outgoingMessage::getVerb()), [$event]);
        }
    }
}
