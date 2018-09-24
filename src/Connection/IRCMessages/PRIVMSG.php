<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class PRIVMSG
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix PRIVMSG #channel :message
 */
class PRIVMSG extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
    use PrefixTrait;
    use ChannelTrait;
    use NicknameTrait;
    use MessageTrait;

    protected static $verb = 'PRIVMSG';

    /**
     * @var bool|string
     */
    protected $ctcpVerb = false;

    /**
     * @var bool
     */
    protected $isCtcp = false;

    /**
     * PRIVMSG constructor.
     *
     * @param string $channel
     * @param string $message
     */
    public function __construct(string $channel, string $message)
    {
        $this->setChannel($channel);
        $this->setMessage($message);
    }

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

        $prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
        $channel = $incomingIrcMessage->getArgs()[0];
        $message = $incomingIrcMessage->getArgs()[1];

        $isCtcp = substr($message, 0, 1) == "\x01" && substr($message, -1, 1) == "\x01";
        $ctcpVerb = false;

        if ($isCtcp) {
            $message = trim(substr($message, 1, -1));
            $message = explode(' ', $message, 2);
            $ctcpVerb = array_shift($message);
            $message = !empty($message) ? array_shift($message) : '';
        }

        $object = new self($channel, $message);
        $object->setPrefix($prefix);
        $object->setIsCtcp($isCtcp);
        $object->setCtcpVerb($ctcpVerb);
        $object->setNickname($prefix->getNickname());

        return $object;
    }

    /**
     * @return bool|string
     */
    public function getCtcpVerb()
    {
        return $this->ctcpVerb;
    }

    /**
     * @param bool|string $ctcpVerb
     */
    public function setCtcpVerb($ctcpVerb)
    {
        $this->ctcpVerb = $ctcpVerb;
    }

    /**
     * @return bool
     */
    public function isCtcp(): bool
    {
        return $this->isCtcp;
    }

    /**
     * @param bool $isCtcp
     */
    public function setIsCtcp(bool $isCtcp)
    {
        $this->isCtcp = $isCtcp;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->isCtcp()) {
            $message = "\x01" . $this->getCtcpVerb() . ' ' . $this->getMessage() . "\x01";
        } else {
            $message = $this->getMessage();
        }

        return 'PRIVMSG ' . $this->getChannel() . ' :' . $message . "\r\n";
    }
}