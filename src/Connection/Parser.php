<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Messages\Exceptions\CastException;
use WildPHP\Messages\Generics\IncomingMessage;
use WildPHP\Messages\Utility\MessageCaster;

class Parser extends BaseModule
{
    use ContainerTrait;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * Parser constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        EventEmitter::fromContainer($container)
            ->on('stream.line.in', [$this, 'parseIncomingIrcLine']);

        EventEmitter::fromContainer($container)
            ->on('stream.data.in', [$this, 'convertDataToLines']);

        $this->setContainer($container);
    }

    /**
     * @param string $data
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function convertDataToLines(string $data)
    {
        // Prepend the buffer, first.
        $data = $this->getBuffer() . $data;

        // Try to split by any combination of \r\n, \r, \n
        $lines = preg_split("/\\r\\n|\\r|\\n/", $data);

        // The last element of this array is always residue.
        $residue = array_pop($lines);
        $this->setBuffer($residue);

        foreach ($lines as $line) {
            Logger::fromContainer($this->getContainer())
                ->debug('<< ' . $line);
            EventEmitter::fromContainer($this->getContainer())
                ->emit('stream.line.in', [$line]);
        }
    }

    /**
     * @return string
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * @param string $buffer
     */
    public function setBuffer(string $buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * @param string $line
     * @throws \ReflectionException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function parseIncomingIrcLine(string $line)
    {
        $parsedLine = static::parseLine($line);
        $args = $parsedLine->args;
        array_shift($args);
        $ircMessage = new IncomingMessage($parsedLine->prefix, $parsedLine->verb, $args);

        $verb = strtolower($ircMessage->getVerb());
        EventEmitter::fromContainer($this->getContainer())
            ->emit('irc.line.in', [$ircMessage, Queue::fromContainer($this->getContainer())]);

        try {
            $castIrcMessage = MessageCaster::castMessage($ircMessage);
            $ircMessage = $castIrcMessage;
        } catch (CastException $exception) {
        }

        EventEmitter::fromContainer($this->getContainer())
            ->emit('irc.line.in.' . $verb, [$ircMessage, Queue::fromContainer($this->getContainer())]);
    }

    /**
     * @param string $line
     *
     * @return ParsedIrcMessage
     */
    public static function parseLine(string $line): ParsedIrcMessage
    {
        $parv = self::split($line);
        $index = 0;
        $parv_count = count($parv);
        $self = new ParsedIrcMessage();

        if ($index < $parv_count && $parv[$index][0] === '@') {
            $tags = _substr($parv[$index], 1);
            $index++;
            foreach (explode(';', $tags) as $item) {
                list($k, $v) = explode('=', $item, 2);
                if ($v === null) {
                    $self->tags[$k] = true;
                } else {
                    $self->tags[$k] = $v;
                }
            }
        }

        if ($index < $parv_count && $parv[$index][0] === ':') {
            $self->prefix = _substr($parv[$index], 1);
            $index++;
        }

        if ($index < $parv_count) {
            $self->verb = strtoupper($parv[$index]);
            $self->args = array_slice($parv, $index);
        }

        return $self;
    }

    /**
     * @param string $line
     *
     * @return array
     */
    public static function split(string $line): array
    {
        $line = rtrim($line, "\r\n");
        $line = explode(' ', $line);
        $index = 0;
        $arv_count = count($line);
        $parv = [];

        while ($index < $arv_count && $line[$index] === '') {
            $index++;
        }

        if ($index < $arv_count && $line[$index][0] == '@') {
            $parv[] = $line[$index];
            $index++;
            while ($index < $arv_count && $line[$index] === '') {
                $index++;
            }
        }

        if ($index < $arv_count && $line[$index][0] == ':') {
            $parv[] = $line[$index];
            $index++;
            while ($index < $arv_count && $line[$index] === '') {
                $index++;
            }
        }

        while ($index < $arv_count) {
            if ($line[$index] === '') {
                ;
            } elseif ($line[$index][0] === ':') {
                break;
            } else {
                $parv[] = $line[$index];
            }
            $index++;
        }

        if ($index < $arv_count) {
            $trailing = implode(' ', array_slice($line, $index));
            $parv[] = _substr($trailing, 1);
        }

        return $parv;
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}

/**
 * @param $str
 * @param $start
 *
 * @return bool|string
 */
function _substr($str, $start)
{
    $ret = substr($str, $start);

    return $ret === false ? '' : $ret;
}