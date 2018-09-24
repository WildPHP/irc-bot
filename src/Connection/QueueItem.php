<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Core\Connection\IRCMessages\SendableMessage;

class QueueItem
{
    /**
     * @var SendableMessage
     */
    protected $commandObject;

    /**
     * @var int
     */
    protected $time;

    /**
     * @var bool
     */
    protected $cancelled = false;

    /**
     * QueueItem constructor.
     *
     * @param SendableMessage $command
     * @param int $time
     */
    public function __construct(SendableMessage $command, int $time)
    {
        $this->setCommandObject($command);
        $this->setTime($time);
    }

    /**
     * @return SendableMessage
     */
    public function getCommandObject(): SendableMessage
    {
        return $this->commandObject;
    }

    /**
     * @param SendableMessage $commandObject
     */
    public function setCommandObject(SendableMessage $commandObject)
    {
        $this->commandObject = $commandObject;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $time
     */
    public function setTime(int $time)
    {
        $this->time = $time;
    }

    /**
     * @return bool
     */
    public function itemShouldBeTriggered(): bool
    {
        return time() >= $this->getTime();
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