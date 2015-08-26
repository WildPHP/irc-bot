<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace WildPHP\IRC\Commands;

// Base for IRC outgoing data.
use WildPHP\IRC\IRCData;
use WildPHP\IRC\MessageLengthException;
use WildPHP\Validation;

class Privmsg extends IRCData
{
    /**
     * The message to send.
     * @var string
     */
    protected $message = '';

    /**
     * The recipient to receive this message.
     * @var string
     */
    protected $recipient = '';

    /**
     * @param string $recipient The person/channel to receive this PRIVMSG.
     * @param string $message The message to send.
     */
    public function __construct($recipient, $message)
    {
        $this->setRecipient($recipient);
        $this->setMessage($message);
    }

    /**
     * Sets the message to send.
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Gets the message.
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the recipient.
     * @param string
     */
    public function setRecipient($recipient)
    {
        if (!Validation::isNickname($recipient) && !Validation::isChannel($recipient))
            throw new \InvalidArgumentException('String passed as recipient to IRC\\Commands\\Privmsg::setRecipient is not a valid nickname or channel.');

        $this->recipient = $recipient;
    }

    /**
     * Gets the recipient.
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    public function __toString()
    {
        if (strlen($this->getMessage()) > 510 - 10 - strlen($this->getRecipient()))
            throw new MessageLengthException('Message passed to IRC\\Commands\\Privmsg is too long.');

        if (empty($this->getRecipient()) || empty($this->getMessage()))
            return null;

        return 'PRIVMSG ' . $this->getRecipient() . ' :' . $this->getMessage();
    }
}