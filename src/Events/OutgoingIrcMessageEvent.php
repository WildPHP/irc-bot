<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Events;

use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

class OutgoingIrcMessageEvent implements EventInterface
{
    /**
     * @var OutgoingMessageInterface
     */
    private $outgoingMessage;

    /**
     * OutgoingIrcMessageEvent constructor.
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
}
