<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;

use Evenement\EventEmitterTrait;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Messages\Authenticate;
use WildPHP\Messages\Generics\IncomingMessage;

class Sasl implements CapabilityInterface
{
    use ContainerTrait;
    use EventEmitterTrait;

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
     * SASL constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        if (!Configuration::fromContainer($container)->offsetExists('sasl') ||
            empty(Configuration::fromContainer($container)['sasl']['username']) ||
            empty(Configuration::fromContainer($container)['sasl']['password'])
        ) {
            Logger::fromContainer($container)
                ->info('[SASL] Not initialized because no credentials were provided.');
            $this->complete = true;

            return;
        }

        CapabilityHandler::fromContainer($container)
            ->requestCapability('sasl');

        EventEmitter::fromContainer($container)
            ->on('irc.cap.acknowledged.sasl', [$this, 'sendAuthenticationMechanism']);
        EventEmitter::fromContainer($container)
            ->on('irc.cap.notAcknowledged.sasl', [$this, 'completeSasl']);
        EventEmitter::fromContainer($container)
            ->on('irc.line.in.authenticate', [$this, 'sendCredentials']);

        // Map all numeric SASL responses to either the success or error handler:
        EventEmitter::fromContainer($container)
            ->on('irc.line.in', [$this, 'handleResponse']);

        Logger::fromContainer($container)
            ->debug('[SASL] Initialized, awaiting server response.');

        $this->setContainer($container);
    }

    /**
     * @param Queue $queue
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function sendAuthenticationMechanism(Queue $queue)
    {
        $queue->authenticate('PLAIN');
        Logger::fromContainer($this->getContainer())
            ->debug('[SASL] Authentication mechanism requested, awaiting server response.');
    }

    /**
     * @param Authenticate $message
     * @param Queue $queue
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function sendCredentials(Authenticate $message, Queue $queue)
    {
        if ($message->getResponse() != '+') {
            return;
        }

        $username = Configuration::fromContainer($this->getContainer())['sasl']['username'];
        $password = Configuration::fromContainer($this->getContainer())['sasl']['password'];

        $credentials = $this->generateCredentialString($username, $password);
        $queue->authenticate($credentials);

        Logger::fromContainer($this->getContainer())
            ->debug('[SASL] Sent authentication details, awaiting response from server.');
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
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function handleResponse(IncomingMessage $message)
    {
        $code = $message->getVerb();

        if (!array_key_exists($code, $this->saslCodes)) {
            return;
        }

        // This event has to fit on the events used in CapabilityHandler.
        Logger::fromContainer($this->getContainer())
            ->info('[SASL] Authentication ended with code ' . $code . ' (' . $this->saslCodes[$code] . ')');

        $this->completeSasl();
    }

    public function completeSasl()
    {
        Logger::fromContainer($this->getContainer())
            ->info('[SASL] Ended.');

        EventEmitter::fromContainer($this->getContainer())
            ->removeListener('irc.line.in', [$this, 'handleResponse']);

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