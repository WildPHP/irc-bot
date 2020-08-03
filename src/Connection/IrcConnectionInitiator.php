<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Connection;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Throwable;
use WildPHP\Core\Queue\IrcMessageQueue;

class IrcConnectionInitiator
{

    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var IrcConnectionInterface
     */
    private $connection;
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * IrcConnectionInitiator constructor.
     * @param LoggerInterface $logger
     * @param EventEmitterInterface $eventEmitter
     * @param IrcMessageQueue $queue
     * @param IrcConnectionInterface $connection
     * @param LoopInterface $loop
     */
    public function __construct(
        LoggerInterface $logger,
        EventEmitterInterface $eventEmitter,
        IrcMessageQueue $queue,
        IrcConnectionInterface $connection,
        LoopInterface $loop
    ) {
        $eventEmitter->on('irc.cap.end', [$this, 'initiateConnection']);

        $this->queue = $queue;
        $this->connection = $connection;
        $this->loop = $loop;
        $this->logger = $logger;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @param IrcConnectionInterface $ircConnection
     * @return void
     */
    public function startConnection(IrcConnectionInterface $ircConnection): void
    {
        $connectionDetails = $ircConnection->getConnectionDetails();

        $promise = $ircConnection->connect(
            ConnectorFactory::create(
                $this->loop,
                $connectionDetails->isSecure(),
                $connectionDetails->getContextOptions()
            )
        );

        $promise->then(null, function (Throwable $e) {
            $this->logger->error('An error occurred in the IRC connection:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->eventEmitter->emit('stream.error');
        });

        $this->eventEmitter->on('stream.closed', [$this->loop, 'stop']);
        $this->eventEmitter->on('stream.error', [$this->loop, 'stop']);
    }

    /**
     * @return void
     */
    public function initiateConnection(): void
    {
        $connectionDetails = $this->connection->getConnectionDetails();
        if (!empty($connectionDetails->getPassword())) {
            $this->queue->pass($connectionDetails->getPassword());
        }

        $this->queue->user(
            $connectionDetails->getUsername(),
            $connectionDetails->getHostname(),
            $connectionDetails->getAddress(),
            $connectionDetails->getRealname()
        );
        $this->queue->nick($connectionDetails->getWantedNickname());
    }
}
