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
 * Class USER
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix USER username hostname servername realname
 */
class USER extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
    protected static $verb = 'USER';

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $hostname = '';

    /**
     * @var string
     */
    protected $servername = '';

    /**
     * @var string
     */
    protected $realname = '';

    /**
     * USER constructor.
     *
     * @param string $username
     * @param string $hostname
     * @param string $servername
     * @param string $realname
     */
    public function __construct(string $username, string $hostname, string $servername, string $realname)
    {
        $this->setUsername($username);
        $this->setHostname($hostname);
        $this->setServername($servername);
        $this->setRealname($realname);
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

        $args = $incomingIrcMessage->getArgs();
        $username = array_shift($args);
        $hostname = array_shift($args);
        $servername = array_shift($args);
        $realname = array_shift($args);

        $object = new self($username, $hostname, $servername, $realname);

        return $object;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $username = $this->getUsername();
        $hostname = $this->getHostname();
        $servername = $this->getServername();
        $realname = $this->getRealname();

        return 'USER ' . $username . ' ' . $hostname . ' ' . $servername . ' :' . $realname . "\r\n";
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname(string $hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return string
     */
    public function getServername(): string
    {
        return $this->servername;
    }

    /**
     * @param string $servername
     */
    public function setServername(string $servername)
    {
        $this->servername = $servername;
    }

    /**
     * @return string
     */
    public function getRealname(): string
    {
        return $this->realname;
    }

    /**
     * @param string $realname
     */
    public function setRealname(string $realname)
    {
        $this->realname = $realname;
    }
}