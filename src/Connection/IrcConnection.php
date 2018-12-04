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
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

class IrcConnection implements IrcConnectionInterface
{

    /**
     * @var Promise
     */
    protected $connectorPromise;

    /**
     * @var ConnectionDetails
     */
    private $connectionDetails;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EventEmitterInterface $eventEmitter
     * @param ConnectionDetails $connectionDetails
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger, ConnectionDetails $connectionDetails)
    {
        $eventEmitter->on('irc.line.in.error', [$this, 'close']);
        $eventEmitter->on('irc.line.out', [$this, 'writeQueueItem']);
        $eventEmitter->on('irc.force.close', [$this, 'close']);

        $this->connectionDetails = $connectionDetails;
        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
    }

    /**
     * @param string $data
     *
     * @return \React\Promise\PromiseInterface
     */
    public function write(string $data): PromiseInterface
    {
        if (substr_count($data, "\r") > 1 || substr_count($data, "\n") > 1) {
            $pieces = explode("\r", str_replace("\n", "\r", $data));

            $this->logger->warning('Multiline message caught! Only sending first line. Please file a bug report, this should not happen.',
                    [
                        'pieces' => $pieces
                    ]);

            $data = $pieces[0] . "\r\n";
        }

        $promise = $this->connectorPromise->then(function (ConnectionInterface $stream) use ($data) {
            $this->eventEmitter->emit('stream.data.out', [$data]);

            $this->logger->debug('>> ' . $data);
            $stream->write($data);
        });

        return $promise;
    }

    /**
     * @param string $data
     */
    public function incomingData(string $data)
    {
        $this->eventEmitter->emit('stream.data.in', [$data]);
    }

    /**
     * @param QueueItem $queueItem
     */
    public function writeQueueItem(QueueItem $queueItem)
    {
        if (!$queueItem->isCancelled()) {
            $this->write($queueItem->getCommandObject());
        }
    }

    /**
     * @param ConnectorInterface $connectorInterface
     *
     * @return \React\Promise\PromiseInterface
     */
    public function connect(ConnectorInterface $connectorInterface)
    {
        $connectionString = $this->getConnectionDetails()->getAddress() . ':' . $this->getConnectionDetails()->getPort();
        $promise = $connectorInterface->connect($connectionString)
            ->then(function (ConnectionInterface $connection) use (&$buffer, $connectionString) {
                $this->eventEmitter->emit('stream.created');

                $connection->on('error',
                    function ($error) use ($connectionString) {
                        throw new ConnectionException('Connection to ' . $connectionString . ' failed: ' . $error);
                    });

                $connection->on('data', [$this, 'incomingData']);

                return $connection;
            });

        $this->connectorPromise = $promise;
        return $promise;
    }

    /**
     * @return \React\Promise\PromiseInterface
     */
    public function close()
    {
        $promise = $this->connectorPromise->then(function (ConnectionInterface $connection) {
            $this->logger->warning('Closing connection...');
            $connection->close();
            $this->eventEmitter->emit('stream.closed');
        });

        return $promise;
    }

    /**
     * @return ConnectionDetails
     */
    public function getConnectionDetails(): ConnectionDetails
    {
        return $this->connectionDetails;
    }
}