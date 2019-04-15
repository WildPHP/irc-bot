<?php
declare(strict_types=1);

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Queue;

use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

class IrcMessageQueueItem implements QueueItemInterface
{
    /**
     * @var OutgoingMessageInterface
     */
    protected $outgoingMessage;

    /**
     * @var bool
     */
    protected $cancelled = false;

    /**
     * QueueItem constructor.
     *
     * @param OutgoingMessageInterface $outgoingMessage
     */
    public function __construct(OutgoingMessageInterface $outgoingMessage)
    {
        $this->outgoingMessage = $outgoingMessage;
    }

    /**
     * @return OutgoingMessageInterface
     */
    public function getOutgoingMessage(): OutgoingMessageInterface
    {
        return $this->outgoingMessage;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * @return void
     */
    public function cancel(): void
    {
        $this->cancelled = true;
    }
}
