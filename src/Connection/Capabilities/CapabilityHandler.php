<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Connection\Capabilities;

use Evenement\EventEmitterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use WildPHP\Core\Events\CapabilityEvent;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Messages\Cap;

class CapabilityHandler
{
    /**
     * Array of built-in capability handlers
     * @var string[]
     */
    private $capabilityHandlers = [
        'account-notify' => AccountNotifyHandler::class,
        'extended-join' => ExtendedJoinHandler::class,
        'multi-prefix' => MultiPrefixHandler::class,
        'sasl' => Sasl::class,
        'userhost-in-names' => UserhostInNamesHandler::class
    ];

    /**
     * @var array
     */
    private $availableCapabilities = [];

    /**
     * @var Deferred[]
     */
    private $queuedCapabilities = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * CapabilityHandler constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcMessageQueue $queue
     * @param ContainerInterface $container
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcMessageQueue $queue,
        ContainerInterface $container
    ) {
        $eventEmitter->on('stream.created', [$this, 'initialize']);
        $eventEmitter->on('irc.msg.in.cap', [$this, 'responseRouter']);
        $eventEmitter->on('irc.cap.ls.final', [$this, 'initializeCapabilityHandlers']);

        $this->logger = $logger;
        $this->queue = $queue;
        $this->eventEmitter = $eventEmitter;
        $this->container = $container;
    }

    public function initialize(): void
    {
        $this->logger->debug('[CapabilityHandler] Capability negotiation started.');
        $this->queue->cap('LS');
    }

    /**
     * @return void
     */
    public function initializeCapabilityHandlers(): void
    {
        foreach ($this->capabilityHandlers as $capability => $handler) {
            if (!$this->isCapabilityAvailable($capability)) {
                $this->logger->debug(sprintf(
                    '[CapabilityHandler] Skipping handler for capability %s because it is not available.',
                    $capability
                ));

                unset($this->capabilityHandlers[$capability]);
                continue;
            }

            $promise = $this->requestCapability($capability);

            /** @var CapabilityInterface $handlerObject */
            $handlerObject = $this->container->get($handler);
            $handlerObject->setRequestPromise($promise);
            $handlerObject->onFinished([$this, 'tryEndNegotiation']);
            $this->capabilityHandlers[$capability] = $handlerObject;
        }
    }

    /**
     * @param string $capability
     *
     * @return Promise|PromiseInterface
     */
    public function requestCapability(string $capability)
    {
        $deferred = new Deferred();
        $this->logger->debug('[CapabilityHandler] Capability requested', ['capability' => $capability]);
        $this->queue->cap('REQ', [$capability]);
        $this->queuedCapabilities[$capability] = $deferred;

        return $deferred->promise();
    }

    /**
     * @param string $capability
     *
     * @return bool
     */
    public function isCapabilityAvailable(string $capability): bool
    {
        return in_array($capability, $this->availableCapabilities, true);
    }

    /**
     * @param IncomingIrcMessageEvent $event
     */
    public function responseRouter(IncomingIrcMessageEvent $event): void
    {
        /** @var Cap $incomingIrcMessage */
        $incomingIrcMessage = $event->getIncomingMessage();

        $command = $incomingIrcMessage->getCommand();
        $capabilities = $incomingIrcMessage->getCapabilities();

        switch ($command) {
            case 'LS':
                $this->updateAvailableCapabilities($capabilities, $incomingIrcMessage->isFinalMessage());
                break;

            case 'ACK':
                $this->resolveCapabilityHandlers($capabilities);
                break;

            case 'NAK':
                $this->rejectCapabilityHandlers($capabilities);
                break;
        }
    }

    /**
     * @param array $capabilities
     * @param bool $finalMessage
     */
    protected function updateAvailableCapabilities(array $capabilities, bool $finalMessage): void
    {
        $this->availableCapabilities = $capabilities;

        $this->logger->debug(
            '[CapabilityHandler] Updated list of available capabilities.',
            [
                'availableCapabilities' => $capabilities
            ]
        );

        $event = new CapabilityEvent($capabilities);
        $this->eventEmitter->emit('irc.cap.ls', [$event]);

        if ($finalMessage) {
            $this->eventEmitter->emit('irc.cap.ls.final', [$event]);
        }
    }

    /**
     * @param string[] $capabilities
     */
    public function resolveCapabilityHandlers(array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            $this->logger->debug('[CapabilityHandler] Capability ' . $capability . ' resolved.');
            if (array_key_exists($capability, $this->queuedCapabilities)) {
                $this->queuedCapabilities[$capability]->resolve();
            }
        }
    }

    /**
     * @param string[] $capabilities
     */
    public function rejectCapabilityHandlers(array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            $this->logger->debug('[CapabilityHandler] Capability ' . $capability . ' rejected.');
            if (array_key_exists($capability, $this->queuedCapabilities)) {
                $this->queuedCapabilities[$capability]->reject();
            }
        }
    }

    /**
     * @return void
     */
    public function tryEndNegotiation(): void
    {
        if (!$this->canEndNegotiation()) {
            return;
        }

        $this->logger->debug('[CapabilityHandler] Ending capability negotiation.');
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
        foreach ($this->capabilityHandlers as $string => $capability) {
            $this->logger->debug(sprintf(
                '[CapabilityHandler] State of capability %s: %s',
                $string,
                ($capability->finished() ? 'finished' : 'not finished')
            ));
            if (!$capability->finished()) {
                return false;
            }
        }

        $this->logger->debug('[CapabilityHandler] All capabilities are ready.');

        return true;
    }
}
