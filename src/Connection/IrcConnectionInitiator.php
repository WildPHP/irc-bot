<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;

class IrcConnectionInitiator
{

    /**
     * @var QueueInterface
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
     * IrcConnectionInitiator constructor.
     * @param EventEmitterInterface $eventEmitter
     * @param QueueInterface $queue
     * @param IrcConnectionInterface $connection
     * @param LoopInterface $loop
     */
    public function __construct(EventEmitterInterface $eventEmitter, QueueInterface $queue, IrcConnectionInterface $connection, LoopInterface $loop)
    {
        $eventEmitter->on('stream.created', [$this, 'sendInitialConnectionDetails']);

        $this->queue = $queue;
        $this->connection = $connection;
        $this->loop = $loop;
    }

    /**
     * @param QueueInterface $queue
     */
    public function initiateConnection(QueueInterface $queue)
    {
        $this->loop->addPeriodicTimer(1, [$this, 'flushQueue']);

        $connectionDetails = $this->connection->getConnectionDetails();
        if (!empty($connectionDetails->getPassword())) {
            $queue->pass($connectionDetails->getPassword());
        }

        $queue->user(
            $connectionDetails->getUsername(),
            $connectionDetails->getHostname(),
            $connectionDetails->getAddress(),
            $connectionDetails->getRealname()
        );
        $queue->nick($connectionDetails->getWantedNickname());
    }
}