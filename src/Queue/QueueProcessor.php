<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

use React\EventLoop\LoopInterface;

class QueueProcessor
{
    /**
     * @var QueueInterface
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
     * QueueProcessor constructor.
     * @param QueueInterface $queue
     * @param LoopInterface $loop
     */
    public function __construct(QueueInterface $queue, LoopInterface $loop)
    {
        $loop->addPeriodicTimer(1, [$this, 'processDueItems']);
        $this->queue = $queue;
    }

    public function processDueItems(): void
    {
        /** @var QueueItemInterface[] $items */
        $items = $this->queue->toArray();
        $amountToProcess = $this->messagesPerSecondAfterBurst;

        // Use up our burst if we haven't used it and
        if (!$this->usedBurst && count($items) > $this->burstTrigger) {
            $amountToProcess = $this->burstAmount;
            $this->usedBurst = true;
        }

        $itemsToProcess = array_slice($items, 0, $amountToProcess);

        // Reset the burst flag when the queue is now empty
        if ($this->usedBurst && count($items) === 0) {
            $this->usedBurst = false;
        }

        if (empty($itemsToProcess)) {
            return;
        }

        foreach ($itemsToProcess as $queueItem) {
            $queueItem->getDeferred()->resolve($queueItem);
            $this->queue->dequeue($queueItem);
        }
    }
}
