<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Core\Connection\IRCMessages\SendableMessage;

interface QueueInterface
{
    /**
     * @param SendableMessage $command
     *
     * @return void
     */
    public function insertMessage(SendableMessage $command);

    /**
     * @param QueueItem $item
     *
     * @return void
     */
    public function removeMessage(QueueItem $item);

    /**
     * @param int $index
     *
     * @return void
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