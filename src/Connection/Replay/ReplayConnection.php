<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Replay;


use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;
use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Connection\IrcConnectionInterface;

class ReplayConnection implements IrcConnectionInterface
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
     * @var ReplayStructure
     */
    private $structure;
    /**
     * @var ConnectionDetails
     */
    private $connectionDetails;

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

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->connectionDetails = $connectionDetails;
    }



    public function connect(ConnectorInterface $connectorInterface): PromiseInterface
    {
        $deferred = new Deferred();

        $promise = $deferred->promise();
        $promise->then(function () {
            $this->eventEmitter->emit('stream.created');
        });
        $deferred->resolve();

        return $promise;
    }

    public function close(): PromiseInterface
    {
        $deferred = new Deferred();

        $promise = $deferred->promise();
        $deferred->resolve();

        return $promise;
    }

    public function getConnectionDetails(): ConnectionDetails
    {
        return $this->connectionDetails;
    }

    public function write(string $data): PromiseInterface
    {
        $deferred = new Deferred();

        $promise = $deferred->promise();
        $promise->then(function() use ($data) {
            $this->eventEmitter->emit('stream.data.out', [$data]);

            $this->logger->debug('REPLAYED >> ' . $data);

            $reply = $this->structure->getReply(trim($data));

            if ($reply !== null) {
                $this->logger->debug('REPLAY: Got reply, executing.');

                $reply($this);
            }

        });
        $deferred->resolve();

        return $promise;
    }

    public function incomingData(string $data): void
    {
        $this->eventEmitter->emit('stream.data.in', [$data]);
    }

    /**
     * @return ReplayStructure
     */
    public function getStructure(): ReplayStructure
    {
        return $this->structure;
    }

    /**
     * @param ReplayStructure $structure
     */
    public function setStructure(ReplayStructure $structure): void
    {
        $this->structure = $structure;
    }
}