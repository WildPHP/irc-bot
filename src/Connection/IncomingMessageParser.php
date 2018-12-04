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
use WildPHP\Messages\Exceptions\CastException;
use WildPHP\Messages\Generics\IncomingMessage;
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
        $ircMessage = new IncomingMessage($parsedLine->prefix, $parsedLine->verb, $args);

        $verb = strtolower($ircMessage->getVerb());
        $this->eventEmitter->emit('irc.line.in', [$ircMessage]);

        try {
            $castIrcMessage = MessageCaster::castMessage($ircMessage);
            $ircMessage = $castIrcMessage;
        } catch (CastException $exception) {
        }

        $this->eventEmitter->emit('irc.line.in.' . $verb, [$ircMessage]);
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