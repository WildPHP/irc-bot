<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Events\UnsupportedIncomingIrcMessageEvent;
use WildPHP\Messages\Exceptions\CastException;
use WildPHP\Messages\Generics\IrcMessage;
use WildPHP\Messages\Utility\MessageCaster;

class IncomingMessageParser
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * IncomingMessageHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger)
    {
        $eventEmitter->on('stream.line.in', [$this, 'parseIncomingIrcLine']);
        $eventEmitter->on('stream.data.in', [$this, 'convertDataToLines']);

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
    }

    /**
     * @param string $line
     * @throws \ReflectionException
     */
    public function parseIncomingIrcLine(string $line)
    {
        $parsedLine = MessageParser::parseLine($line);
        $args = $parsedLine->args;
        array_shift($args);
        $ircMessage = new IrcMessage($parsedLine->prefix, $parsedLine->verb, $args);

        $verb = strtolower($ircMessage->getVerb());

        try {
            $castIrcMessage = MessageCaster::castMessage($ircMessage);
            $ircMessage = $castIrcMessage;
            $this->eventEmitter->emit('irc.msg.in', [new IncomingIrcMessageEvent($ircMessage)]);
            $this->eventEmitter->emit('irc.msg.in.' . $verb, [new IncomingIrcMessageEvent($ircMessage)]);
        } catch (CastException $exception) {
            $this->logger->debug(sprintf('Received message with verb %s but it could not be cast to an implemented message type. This message is not supported!',
                $verb));

            $this->eventEmitter->emit('irc.msg.in.unsupported', [new UnsupportedIncomingIrcMessageEvent($ircMessage)]);
        }
    }

    /**
     * @param string $data
     */
    public function convertDataToLines(string $data)
    {
        // Prepend the buffer, first.
        $data = $this->buffer . $data;

        // Try to split by any combination of \r\n, \r, \n
        $lines = preg_split("/\\r\\n|\\r|\\n/", $data);

        // The last element of this array is always residue.
        $residue = array_pop($lines);
        $this->buffer = $residue;

        foreach ($lines as $line) {
            $this->logger->debug('<< ' . $line);
            $this->eventEmitter->emit('stream.line.in', [$line]);
        }
    }
}