<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use React\EventLoop\LoopInterface;

class QueueProcessor
{
    /**
     * @var QueueInterface
     */
    private $queue;

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

    public function processDueItems()
    {
        $items = $this->queue->getDueItems();
        $this->queue->processQueueItems($items);
    }
}