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
 * Class RPL_ENDOFNAMES
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: :server 366 nickname #channel :End of /NAMES list.
 */
class RPL_ENDOFNAMES extends BaseIRCMessage implements ReceivableMessage
{
    use NicknameTrait;
    use ChannelTrait;
    use MessageTrait;

    protected static $verb = '366';

    /**
     * @param IncomingIrcMessage $incomingIrcMessage
     *
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
    {
        if ($incomingIrcMessage->getVerb() != self::getVerb()) {
            throw new \InvalidArgumentException('Expected incoming ' . self::getVerb() . '; got ' . $incomingIrcMessage->getVerb());
        }

        $args = $incomingIrcMessage->getArgs();
        $nickname = array_shift($args);
        $channel = array_shift($args);
        $message = array_shift($args);

        $object = new self();
        $object->setNickname($nickname);
        $object->setChannel($channel);
        $object->setMessage($message);

        return $object;
    }
}