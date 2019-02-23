<?php

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use React\Promise\PromiseInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Events\UnsupportedIncomingIrcMessageEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Messages\Authenticate;

class Sasl implements CapabilityInterface
{
    /**
     * @var bool
     */
    private $complete = false;

    /**
     * @var array
     */
    public static $saslCodes = [
        902 => 'ERR_NICKLOCKED',
        903 => 'RPL_SASLSUCCESS',
        904 => 'ERR_SASLFAIL',
        905 => 'ERR_SASLTOOLONG',
        906 => 'ERR_SASLABORTED',
        907 => 'ERR_SASLALREADY',
        908 => 'RPL_SASLMECHS'
    ];

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var callable
     */
    private $callback;

    /**
     * SASL constructor.
     *
     * @param IrcMessageQueue $queue
     * @param Configuration $configuration
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(
        IrcMessageQueue $queue,
        Configuration $configuration,
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger
    )
    {
        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->queue = $queue;
    }

    /**
     */
    public function initialize(): void
    {
        if (empty($this->configuration['connection']['sasl']) ||
            empty($this->configuration['connection']['sasl']['username']) ||
            empty($this->configuration['connection']['sasl']['password'])
        ) {
            $this->logger->info('[SASL] Not used because no credentials were provided.');
            $this->completeSasl();

            return;
        }

        $this->eventEmitter->on('irc.msg.in.authenticate', [$this, 'sendCredentials']);

        // Map all numeric SASL responses to either the success or error handler:
        $this->eventEmitter->on('irc.msg.in.unsupported', [$this, 'handleResponse']);

        $this->queue->authenticate('PLAIN');
        $this->logger->debug('[SASL] Authentication mechanism requested, awaiting server response.');
    }

    /**
     * @param IncomingIrcMessageEvent $event
     */
    public function sendCredentials(IncomingIrcMessageEvent $event): void
    {
        /** @var Authenticate $message */
        $message = $event->getIncomingMessage();
        if ($message->getResponse() !== '+') {
            return;
        }

        $username = $this->configuration['connection']['sasl']['username'];
        $password = $this->configuration['connection']['sasl']['password'];

        $credentials = $this->generateCredentialString($username, $password);
        $this->queue->authenticate($credentials);

        $this->logger->debug('[SASL] Sent authentication details, awaiting response from server.');
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return string
     */
    protected function generateCredentialString(string $username, string $password): string
    {
        return base64_encode($username . "\0" . $username . "\0" . $password);
    }

    /**
     * @param UnsupportedIncomingIrcMessageEvent $event
     */
    public function handleResponse(UnsupportedIncomingIrcMessageEvent $event): void
    {
        $message = $event->getMessage();
        $code = $message->getVerb();

        if (!array_key_exists($code, self::$saslCodes)) {
            return;
        }

        // This event has to fit on the events used in CapabilityHandler.
        $this->logger->info('[SASL] Authentication ended with code ' . $code . ' (' . self::$saslCodes[$code] . ')');

        $this->completeSasl();
    }

    /**
     * @return void
     */
    public function completeSasl(): void
    {
        $this->logger->info('[SASL] Ended.');
        $this->eventEmitter->removeListener('irc.msg.in.unsupported', [$this, 'handleResponse']);
        $this->complete = true;
        ($this->callback)();
    }

    /**
     * @return bool
     */
    public function finished(): bool
    {
        return $this->complete;
    }

    /**
     * @param PromiseInterface $promise
     * @return void
     */
    public function setRequestPromise(PromiseInterface $promise): void
    {
        $promise->then([$this, 'initialize'], [$this, 'completeSasl']);
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function onFinished(callable $callback): void
    {
        $this->callback = $callback;
    }
}