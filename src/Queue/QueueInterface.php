<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

/**
 * Interface QueueInterface
 * @package WildPHP\Core\Queue
 */
interface QueueInterface
{
    /**
     * @param QueueItemInterface $queueItem
     */
    public function enqueue(QueueItemInterface $queueItem): void;

    /**
     * @param QueueItemInterface $queueItem
     */
    public function dequeue(QueueItemInterface $queueItem): void;

    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @return QueueItemInterface[]
     */
    public function toArray(): array;
}
