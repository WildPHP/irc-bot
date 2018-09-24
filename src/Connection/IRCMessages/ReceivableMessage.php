<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Interface ReceivableMessage
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * A syntax sample is included with all supported messages.
 */
interface ReceivableMessage
{
    /**
     * @param IncomingIrcMessage $incomingIrcMessage
     *
     * @return mixed
     */
    public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage);

    /**
     * @return string
     */
    public static function getVerb(): string;
}