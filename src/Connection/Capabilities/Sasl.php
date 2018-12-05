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
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Messages\Authenticate;
use WildPHP\Messages\Generics\IncomingMessage;

class Sasl implements CapabilityInterface
{
    /**
     * @var bool
     */
    protected $isAuthenticated = false;

    /**
     * @var bool
     */
    protected $failed = false;

    /**
     * @var bool
     */
    protected $complete = false;

    /**
     * @var array
     */
    protected $saslCodes = [
        '903' => 'RPL_SASLSUCCESS',
        '908' => 'RPL_SASLMECHS',

        '902' => 'ERR_NICKLOCKED',
        '904' => 'ERR_SASLFAIL',
        '905' => 'ERR_SASLTOOLONG',
        '906' => 'ERR_SASLABORTED',
        '907' => 'ERR_SASLALREADY'
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
     * @var QueueInterface
     */
    private $queue;

    /**
     * SASL constructor.
     *
     * @param QueueInterface $queue
     * @param Configuration $configuration
     * @param CapabilityHandler $capabilityHandler
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(QueueInterface $queue, Configuration $configuration, CapabilityHandler $capabilityHandler, EventEmitterInterface $eventEmitter, LoggerInterface $logger)
    {
        if (!$configuration->offsetExists('sasl') ||
            empty($configuration['sasl']['username']) ||
            empty($configuration['sasl']['password'])
        ) {
            $logger->info('[SASL] Not initialized because no credentials were provided.');
            $this->complete = true;

            return;
        }

        $capabilityHandler->requestCapability('sasl');

        $eventEmitter->on('irc.cap.acknowledged.sasl', [$this, 'sendAuthenticationMechanism']);
        $eventEmitter->on('irc.cap.notAcknowledged.sasl', [$this, 'completeSasl']);
        $eventEmitter->on('irc.line.in.authenticate', [$this, 'sendCredentials']);

        // Map all numeric SASL responses to either the success or error handler:
        $eventEmitter->on('irc.line.in', [$this, 'handleResponse']);

        $logger->debug('[SASL] Initialized, awaiting server response.');

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->queue = $queue;
    }

    /**
     * @param QueueInterface $queue
     */
    public function sendAuthenticationMechanism()
    {
        $this->queue->authenticate('PLAIN');
        $this->logger->debug('[SASL] Authentication mechanism requested, awaiting server response.');
    }

    /**
     * @param Authenticate $message
     * @param QueueInterface $queue
     */
    public function sendCredentials(Authenticate $message)
    {
        if ($message->getResponse() != '+') {
            return;
        }

        $username = $this->configuration['sasl']['username'];
        $password = $this->configuration['sasl']['password'];

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
    protected function generateCredentialString(string $username, string $password)
    {
        return base64_encode($username . "\0" . $username . "\0" . $password);
    }

    /**
     * @param IncomingMessage $message
     */
    public function handleResponse(IncomingMessage $message)
    {
        $code = $message->getVerb();

        if (!array_key_exists($code, $this->saslCodes)) {
            return;
        }

        // This event has to fit on the events used in CapabilityHandler.
        $this->logger->info('[SASL] Authentication ended with code ' . $code . ' (' . $this->saslCodes[$code] . ')');

        $this->completeSasl();
    }

    /**
     * @return void
     */
    public function completeSasl()
    {
        $this->logger->info('[SASL] Ended.');
        $this->eventEmitter->removeListener('irc.line.in', [$this, 'handleResponse']);
        $this->complete = true;
    }

    /**
     * @return bool
     */
    public function finished(): bool
    {
        return $this->complete;
    }

    /**
     * @return array
     */
    public function getCapabilities(): array
    {
        return ['sasl'];
    }
}