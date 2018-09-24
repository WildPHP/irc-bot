<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


abstract class BaseIRCMessage
{
    /**
     * @var string
     */
    protected static $verb;

    /**
     * Additional data to be sent with the message.
     * @var array
     */
    protected $messageParameters = [];

    /**
     * @return string
     */
    public static function getVerb(): string
    {
        return static::$verb;
    }

    /**
     * @return array
     */
    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }

    /**
     * @param array $messageParameters
     */
    public function setMessageParameters(array $messageParameters)
    {
        $this->messageParameters = $messageParameters;
    }
}