<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

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

namespace WildPHP\Core\Connection;

use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\SocketClient\ConnectorInterface;
use React\Stream\Stream;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

class IrcConnection
{
    /**
     * @var Promise
     */
    protected $connectorPromise = null;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @param string $buffer
     */
    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
    }
    
    public function registerQueueFlusher(LoopInterface $loop, QueueInterface $queue)
    {
        $loop->addPeriodicTimer(1, function () use ($queue)
        {
            $queueItems = $queue->flush();
            
            foreach ($queueItems as $item)
            {
                $this->write($item->getCommandObject()->formatMessage());
            }
        });
    }

    public function __construct()
    {
        EventEmitter::on('stream.data.in', function ($data)
        {
            // Prepend the buffer, first.
            $data = $this->getBuffer() . $data;

            // Try to split by any combination of \r\n, \r, \n
            $lines = preg_split("/\\r\\n|\\r|\\n/", $data);

            // The last element of this array is always residue.
            $residue = array_pop($lines);
            $this->setBuffer($residue);

            foreach ($lines as $line)
            {
                Logger::debug('<< ' . $line);
                EventEmitter::emit('stream.line.in', [$line]);
            }
        });
    }

    public function createFromConnector(ConnectorInterface $connectorInterface)
    {
        $host = ConnectionDetailsHolder::getServer();
        $port = ConnectionDetailsHolder::getPort();
        
        $this->connectorPromise = $connectorInterface->create($host, $port)
            ->then(function (Stream $stream) use ($host, $port, &$buffer)
            {
                $stream->on('error', function ($error) use ($host, $port)
                {
                    throw new \ErrorException('Connection to host ' . $host . ':' . $port . ' failed: ' . $error);
                });

                $stream->on('data', function ($data)
                {
                    EventEmitter::emit('stream.data.in', [$data]);
                });

                return $stream;
            });
    }

    public function write(string $data)
    {
        $this->connectorPromise->then(function (Stream $stream) use ($data)
        {
            EventEmitter::emit('stream.data.out', [$data]);
            Logger::debug('>> ' . $data);
            $stream->write($data);
        });
    }

    public function close()
    {
        $this->connectorPromise->then(function (Stream $stream)
        {
            $stream->close();
        });
    }
}