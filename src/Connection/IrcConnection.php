<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

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
     * @param LoggerInterface $logger
     * @param ConnectionDetails $connectionDetails
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        ConnectionDetails $connectionDetails
    ) {
        $eventEmitter->on('irc.msg.in.error', [$this, 'close']);

        $this->connectionDetails = $connectionDetails;
        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
    }

    /**
     * @param string $data
     *
     * @return PromiseInterface
     */
    public function write(string $data): PromiseInterface
    {
        if (substr_count($data, "\r") > 1 || substr_count($data, "\n") > 1) {
            $pieces = explode("\r", str_replace("\n", "\r", $data));

            $this->logger->warning(
                'Multiline message caught! Only sending first line. Please file a bug report, this should not happen.',
                [
                    'pieces' => $pieces
                ]
            );

            $data = $pieces[0] . "\r\n";
        }

        return $this->connectorPromise->then(function (ConnectionInterface $stream) use ($data) {
            $this->eventEmitter->emit('stream.data.out', [$data]);

            $this->logger->debug('>> ' . $data);
            $stream->write($data);
        });
    }

    /**
     * @param string $data
     */
    public function incomingData(string $data): void
    {
        $this->eventEmitter->emit('stream.data.in', [$data]);
    }

    /**
     * @param ConnectorInterface $connectorInterface
     *
     * @return PromiseInterface
     */
    public function connect(ConnectorInterface $connectorInterface): PromiseInterface
    {
        $promise = $connectorInterface->connect($this->connectionDetails->getConnectionString())
            ->then(function (ConnectionInterface $connection) {
                $this->eventEmitter->emit('stream.created');

                $connection->on(
                    'error',
                    static function ($error) {
                        throw new ConnectionException(
                            sprintf(
                                'Connection to %s failed: %s',
                                $this->connectionDetails->getConnectionString(),
                                $error
                            )
                        );
                    }
                );

                $connection->on('data', [$this, 'incomingData']);

                return $connection;
            });

        $this->connectorPromise = $promise;
        return $promise;
    }

    /**
     * @return PromiseInterface
     */
    public function close(): PromiseInterface
    {
        return $this->connectorPromise->then(function (ConnectionInterface $connection) {
            $this->logger->warning('Closing connection...');
            $connection->close();
            $this->eventEmitter->emit('stream.closed');
        });
    }

    /**
     * @return ConnectionDetails
     */
    public function getConnectionDetails(): ConnectionDetails
    {
        return $this->connectionDetails;
    }
}
