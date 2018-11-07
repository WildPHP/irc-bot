<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

interface QueueInterface
{
    /**
     * @param OutgoingMessageInterface $command
     *
     * @return QueueItem
     */
    public function insertMessage(OutgoingMessageInterface $command): QueueItem;

    /**
     * @param QueueItem $item
     *
     * @return bool
     */
    public function removeMessage(QueueItem $item);

    /**
     * @param int $index
     *
     * @return bool
     */
    public function removeMessageByIndex(int $index);

    /**
     * @param QueueItem $item
     *
     * @return void
     */
    public function scheduleItem(QueueItem $item);

    /**
     * @return QueueItem[]
     */
    public function flush(): array;
}