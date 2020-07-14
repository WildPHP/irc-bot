<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

use WildPHP\Messages\Interfaces\OutgoingMessageInterface;
use WildPHP\Queue\BaseCancellableQueueItem;

class IrcMessageQueueItem extends BaseCancellableQueueItem
{
    /**
     * @var OutgoingMessageInterface
     */
    protected $outgoingMessage;

    /**
     * QueueItem constructor.
     *
     * @param OutgoingMessageInterface $outgoingMessage
     */
    public function __construct(OutgoingMessageInterface $outgoingMessage)
    {
        parent::__construct();
        $this->outgoingMessage = $outgoingMessage;
    }

    /**
     * @return OutgoingMessageInterface
     */
    public function getOutgoingMessage(): OutgoingMessageInterface
    {
        return $this->outgoingMessage;
    }
}
