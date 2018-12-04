<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Messages\Cap;

class CapabilityHandler
{

    /**
     * Array of built-in capability handlers
     * @var string[]
     */
    protected $capabilities = [
        Sasl::class
    ];

    /**
     * @var array
     */
    protected $availableCapabilities = [];

    /**
     * @var array
     */
    protected $queuedCapabilities = [];

    /**
     * @var array
     */
    protected $acknowledgedCapabilities = [];

    /**
     * @var array
     */
    protected $notAcknowledgedCapabilities = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * CapabilityHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param QueueInterface $queue
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger, QueueInterface $queue)
    {
        $eventEmitter->on('irc.line.in.cap', [$this, 'responseRouter']);
        $eventEmitter->on('irc.cap.ls', [$this, 'flushRequestQueue']);
        $eventEmitter->on('irc.line.in', [$this, 'tryEndNegotiation']);

        $this->requestCapability('extended-join');
        $this->requestCapability('account-notify');
        $this->requestCapability('multi-prefix');

        $this->initializeCapabilityHandlers();

        $logger->debug('[CapabilityHandler] Capability negotiation started.');
        $queue->cap('LS');

        $this->logger = $logger;
        $this->queue = $queue;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @return void
     * @todo Dependency injection.
     */
    public function initializeCapabilityHandlers()
    {
        foreach ($this->capabilities as $capability) {
            /** @var CapabilityInterface $capability */
            $capability = new $capability();
            $capabilities = $capability->getCapabilities();
            $this->requestCapabilities($capabilities);
        }
    }

    /**
     * @param array $capabilities
     */
    public function requestCapabilities(array $capabilities)
    {
        foreach ($capabilities as $capability) {
            $this->requestCapability($capability);
        }
    }

    /**
     * @param string $capability
     *
     * @return bool
     */
    public function requestCapability(string $capability)
    {
        if ($this->isCapabilityAcknowledged($capability) || in_array($capability, $this->queuedCapabilities)) {
            return false;
        }

        $this->logger->debug('Capability queued for request on next flush.', ['capability' => $capability]);

        $this->queuedCapabilities[] = $capability;
        return true;
    }

    /**
     * @param string $capability
     *
     * @return bool
     */
    public function isCapabilityAcknowledged(string $capability): bool
    {
        return in_array($capability, $this->acknowledgedCapabilities);
    }

    /**
     */
    public function flushRequestQueue()
    {
        if (empty($this->queuedCapabilities)) {
            return;
        }

        $capabilities = $this->queuedCapabilities;
        foreach ($capabilities as $key => $capability) {
            if (!$this->isCapabilityAvailable($capability)) {
                unset($capabilities[$key]);
            }
        }

        $this->logger->debug('Sending capability request.', ['queuedCapabilities' => $capabilities]);

        $this->queue->cap('REQ', $capabilities);
    }

    /**
     * @param string $capability
     *
     * @return bool
     */
    public function isCapabilityAvailable(string $capability): bool
    {
        return in_array($capability, $this->availableCapabilities);
    }

    /**
     * @param CAP $incomingIrcMessage
     * @param QueueInterface $queue
     */
    public function responseRouter(CAP $incomingIrcMessage, QueueInterface $queue)
    {
        $command = $incomingIrcMessage->getCommand();
        $capabilities = $incomingIrcMessage->getCapabilities();

        switch ($command) {
            case 'LS':
                $this->updateAvailableCapabilities($capabilities, $queue);
                break;

            case 'ACK':
                $this->updateAcknowledgedCapabilities($capabilities, $queue);
                break;

            case 'NAK':
                $this->updateNotAcknowledgedCapabilities($capabilities, $queue);
                break;
        }
    }

    /**
     * @param array $capabilities
     * @param QueueInterface $queue
     */
    protected function updateAvailableCapabilities(array $capabilities, QueueInterface $queue)
    {
        $this->availableCapabilities = $capabilities;

        $this->logger->debug('Updated list of available capabilities.',
            [
                'availableCapabilities' => $capabilities
            ]);

        foreach ($this->queuedCapabilities as $key => $capability) {
            if (in_array($capability, $capabilities)) {
                continue;
            }

            unset($this->queuedCapabilities[$key]);
            $this->logger->debug('Removed requested capability from the queue because server does not support it.',
                [
                    'capability' => $capability
                ]);
        }

        $this->eventEmitter->emit('irc.cap.ls', [$capabilities, $queue]);
    }

    /**
     * @param string[] $capabilities
     * @param QueueInterface $queue
     */
    public function updateAcknowledgedCapabilities(array $capabilities, QueueInterface $queue)
    {
        $ackCapabilities = array_filter(array_unique(array_merge($this->getAcknowledgedCapabilities(), $capabilities)));
        $this->acknowledgedCapabilities = $ackCapabilities;

        foreach ($ackCapabilities as $capability) {
            $this->eventEmitter->emit('irc.cap.acknowledged.' . $capability, [$queue]);

            if (in_array($capability, $this->queuedCapabilities)) {
                unset($this->queuedCapabilities[array_search($capability, $this->queuedCapabilities)]);
            }
        }

        $this->eventEmitter->emit('irc.cap.acknowledged', [$ackCapabilities, $queue]);
    }

    /**
     * @return array
     */
    public function getAcknowledgedCapabilities(): array
    {
        return $this->acknowledgedCapabilities;
    }

    /**
     * @param string[] $capabilities
     * @param QueueInterface $queue
     */
    public function updateNotAcknowledgedCapabilities(array $capabilities, QueueInterface $queue)
    {
        $nakCapabilities = array_filter(array_unique(array_merge($this->getNotAcknowledgedCapabilities(),
            $capabilities)));
        $this->notAcknowledgedCapabilities = $nakCapabilities;

        foreach ($nakCapabilities as $capability) {
            $this->eventEmitter->emit('irc.cap.notAcknowledged.' . $capability, [$queue]);

            if (in_array($capability, $this->queuedCapabilities)) {
                unset($this->queuedCapabilities[array_search($capability, $this->queuedCapabilities)]);
            }
        }

        $this->eventEmitter->emit('irc.cap.notAcknowledged', [$nakCapabilities, $queue]);
    }

    /**
     * @return array
     */
    public function getNotAcknowledgedCapabilities(): array
    {
        return $this->notAcknowledgedCapabilities;
    }

    /**
     * @return void
     */
    public function tryEndNegotiation(): void
    {
        if (!$this->canEndNegotiation()) {
            return;
        }

        $this->logger->debug('Ending capability negotiation.');
        $this->queue->cap('END');

        $this->eventEmitter->emit('irc.cap.end');
        $this->eventEmitter->removeListener('irc.line.in', [$this, 'tryEndNegotiation']);
    }

    /**
     * @return bool
     */
    public function canEndNegotiation(): bool
    {
        /** @var CapabilityInterface $capability */
        foreach ($this->capabilities as $capability) {
            if (!$capability->finished()) {
                return false;
            }
        }

        return empty($this->queuedCapabilities);
    }

    /**
     * @return array
     */
    public function getAvailableCapabilities(): array
    {
        return $this->availableCapabilities;
    }
}