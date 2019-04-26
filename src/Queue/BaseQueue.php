<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class BaseQueue implements QueueInterface
{
    /**
     * @var IrcMessageQueueItem[]
     */
    protected $messageQueue = [];

    /**
     * @param QueueItemInterface $queueItem
     * @return PromiseInterface
     */
    public function enqueue(QueueItemInterface $queueItem): PromiseInterface
    {
        $queueItem->setDeferred(new Deferred());
        $this->messageQueue[] = $queueItem;
        return $queueItem->getPromise();
    }

    /**
     * @param QueueItemInterface $queueItem
     * @throws QueueException
     */
    public function dequeue(QueueItemInterface $queueItem): void
    {
        if (!in_array($queueItem, $this->messageQueue, true)) {
            throw new QueueException('Given queue item is not found in this queue.');
        }

        $queueItem->getDeferred()->reject();
        unset($this->messageQueue[array_search($queueItem, $this->messageQueue, true)]);
    }

    /**
     * @return QueueItemInterface[]
     */
    public function toArray(): array
    {
        return $this->messageQueue;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->messageQueue = [];
    }
}
