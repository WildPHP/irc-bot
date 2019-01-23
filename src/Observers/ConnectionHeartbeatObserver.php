<?php

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IrcConnectionInterface;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
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
     * @var IrcMessageQueue
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
     * @var IrcConnectionInterface
     */
    private $ircConnection;

    /**
     * PingPongHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param IrcMessageQueue $queue
     * @param LoggerInterface $logger
     * @param LoopInterface $loop
     * @param Configuration $configuration
     * @param IrcConnectionInterface $ircConnection
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        IrcMessageQueue $queue,
        LoggerInterface $logger,
        LoopInterface $loop,
        Configuration $configuration,
        IrcConnectionInterface $ircConnection
    )
    {
        $eventEmitter->on('irc.msg.in', [$this, 'updateLastMessageReceived']);
        $eventEmitter->on('irc.msg.in.ping', [$this, 'respondPong']);
        $loop->addPeriodicTimer($this->loopInterval, [$this, 'connectionHeartbeat']);

        $this->updateLastMessageReceived();

        $this->queue = $queue;
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->ircConnection = $ircConnection;
    }

    public function updateLastMessageReceived(): void
    {
        $this->lastMessageReceived = time();
        $this->hasSentPing = false;
    }

    /**
     * @return void
     */
    public function connectionHeartbeat(): void
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
    protected function forceDisconnect(): void
    {
        $this->logger->warning('The server has not responded to the last PING command. Is the network down? Closing link.');
        $this->queue->quit('No vital signs detected, closing link...');
        $this->ircConnection->close();
    }

    /**
     * @return void
     */
    protected function sendPing(): void
    {
        $this->logger->debug('No message received from the server in the last ' . $this->pingInterval . ' seconds. Sending PING.');

        $serverHostname = $this->configuration['serverConfig']['hostname'];
        $this->queue->ping($serverHostname);
        $this->hasSentPing = true;
    }

    /**
     * @param IncomingIrcMessageEvent $event
     */
    public function respondPong(IncomingIrcMessageEvent $event): void
    {
        /** @var Ping $pingMessage */
        $pingMessage = $event->getIncomingMessage();
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