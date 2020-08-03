<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Events;

use WildPHP\Messages\Interfaces\IncomingMessageInterface;

class IncomingIrcMessageEvent implements EventInterface
{
    /**
     * @var IncomingMessageInterface
     */
    private $incomingMessage;

    /**
     * IncomingIrcMessageEvent constructor.
     * @param IncomingMessageInterface $incomingMessage
     */
    public function __construct(IncomingMessageInterface $incomingMessage)
    {
        $this->incomingMessage = $incomingMessage;
    }

    /**
     * @return IncomingMessageInterface
     */
    public function getIncomingMessage(): IncomingMessageInterface
    {
        return $this->incomingMessage;
    }
}
