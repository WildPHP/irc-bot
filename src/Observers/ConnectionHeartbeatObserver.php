<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Messages\Ping;

class ConnectionHeartbeatObserver
{

    /**
     * @var int
     */
    protected $lastMessageReceived = 0;

    /**
     * The amount of seconds per time the checking loop is run.
     * Do not set this too high or the ping handler won't be effective.
     * @var int
     */
    protected $loopInterval = 1;

    /**
     * In seconds.
     * @var int
     */
    protected $pingInterval = 180;

    /**
     * In seconds.
     * @var int
     */
    protected $disconnectInterval = 120;

    /**
     * @var bool
     */
    protected $hasSentPing = false;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * PingPongHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param QueueInterface $queue
     * @param LoggerInterface $logger
     * @param LoopInterface $loop
     * @param Configuration $configuration
     */
    public function __construct(EventEmitterInterface $eventEmitter, QueueInterface $queue, LoggerInterface $logger, LoopInterface $loop, Configuration $configuration)
    {
        $eventEmitter->on('irc.line.in', [$this, 'updateLastMessageReceived']);
        $eventEmitter->on('irc.line.in.ping', [$this, 'respondPong']);
        $loop->addPeriodicTimer($this->loopInterval, [$this, 'connectionHeartbeat']);

        $this->updateLastMessageReceived();

        $this->eventEmitter = $eventEmitter;
        $this->queue = $queue;
        $this->logger = $logger;
        $this->configuration = $configuration;
    }

    public function updateLastMessageReceived()
    {
        $this->lastMessageReceived = time();
        $this->hasSentPing = false;
    }

    /**
     * @return void
     */
    public function connectionHeartbeat()
    {
        $currentTime = time();

        $disconnectTime = $this->lastMessageReceived + $this->pingInterval + $this->disconnectInterval;
        $shouldDisconnect = $currentTime >= $disconnectTime;

        if ($shouldDisconnect) {
            $this->forceDisconnect();
            return;
        }

        $scheduledPingTime = $this->lastMessageReceived + $this->pingInterval;
        $shouldSendPing = $currentTime >= $scheduledPingTime && !$this->hasSentPing;

        if ($shouldSendPing) {
            $this->sendPing();
        }
    }

    /**
     * @return void
     */
    protected function forceDisconnect()
    {
        $this->logger->warning('The server has not responded to the last PING command. Is the network down? Closing link.');
        $this->queue->quit('No vital signs detected, closing link...');
        $this->eventEmitter->emit('irc.force.close');
    }

    /**
     * @return void
     */
    protected function sendPing()
    {
        $this->logger->debug('No message received from the server in the last ' . $this->pingInterval . ' seconds. Sending PING.');

        $serverHostname = $this->configuration['serverConfig']['hostname'];
        $this->queue->ping($serverHostname);
        $this->hasSentPing = true;
    }

    /**
     * @param PING $pingMessage
     */
    public function respondPong(PING $pingMessage)
    {
        $this->queue->pong($pingMessage->getServer1(), $pingMessage->getServer2());
    }

    /**
     * @return int
     */
    public function getLastMessageReceivedTime(): int
    {
        return $this->lastMessageReceived;
    }
}