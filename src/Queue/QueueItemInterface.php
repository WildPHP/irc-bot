<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

interface QueueItemInterface
{
    /**
     * @return bool
     */
    public function isCancelled(): bool;

    /**
     * @return void
     */
    public function cancel(): void;

    /**
     * @return OutgoingMessageInterface
     */
    public function getOutgoingMessage(): OutgoingMessageInterface;
}
