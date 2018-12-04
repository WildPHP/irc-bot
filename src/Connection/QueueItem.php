<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

class QueueItem
{
    /**
     * @var OutgoingMessageInterface
     */
    protected $commandObject;

    /**
     * @var int
     */
    protected $scheduledTime;

    /**
     * @var bool
     */
    protected $cancelled = false;

    /**
     * QueueItem constructor.
     *
     * @param OutgoingMessageInterface $command
     * @param int $time
     */
    public function __construct(OutgoingMessageInterface $command, int $time)
    {
        $this->setCommandObject($command);
        $this->setScheduledTime($time);
    }

    /**
     * @return OutgoingMessageInterface
     */
    public function getCommandObject(): OutgoingMessageInterface
    {
        return $this->commandObject;
    }

    /**
     * @param OutgoingMessageInterface $commandObject
     */
    public function setCommandObject(OutgoingMessageInterface $commandObject)
    {
        $this->commandObject = $commandObject;
    }

    /**
     * @return bool
     */
    public function itemShouldBeTriggered(): bool
    {
        return time() >= $this->getScheduledTime();
    }

    /**
     * @return int
     */
    public function getScheduledTime(): int
    {
        return $this->scheduledTime;
    }

    /**
     * @param int $scheduledTime
     */
    public function setScheduledTime(int $scheduledTime)
    {
        $this->scheduledTime = $scheduledTime;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * @param bool $cancelled
     */
    public function setCancelled(bool $cancelled)
    {
        $this->cancelled = $cancelled;
    }
}