<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Messages\RPL\ISupport;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class IrcConnection implements ComponentInterface
{
    use ComponentTrait;
    use ContainerTrait;

    /**
     * @var Promise
     */
    protected $connectorPromise;

    /**
     * @var ConnectionDetails
     */
    protected $connectionDetails;

    /**
     * @param ComponentContainer $container
     * @param ConnectionDetails $connectionDetails
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container, ConnectionDetails $connectionDetails)
    {
        EventEmitter::fromContainer($container)
            ->on('irc.line.in.005', [$this, 'handleServerConfig']);

        EventEmitter::fromContainer($container)
            ->on('irc.line.in.error', [$this, 'close']);

        EventEmitter::fromContainer($container)
            ->on('irc.force.close', [$this, 'close']);

        EventEmitter::fromContainer($container)
            ->on('stream.created', [$this, 'sendInitialConnectionDetails']);

        $this->setContainer($container);
        $this->setConnectionDetails($connectionDetails);
    }

    /**
     * @param Queue $queue
     */
    public function sendInitialConnectionDetails(Queue $queue)
    {
        $this->getContainer()->getLoop()
            ->addPeriodicTimer(1, [$this, 'flushQueue']);

        $connectionDetails = $this->getConnectionDetails();
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

    /**
     * @return ConnectionDetails
     */
    public function getConnectionDetails(): ConnectionDetails
    {
        return $this->connectionDetails;
    }

    /**
     * @param ConnectionDetails $connectionDetails
     */
    public function setConnectionDetails(ConnectionDetails $connectionDetails)
    {
        $this->connectionDetails = $connectionDetails;
    }

    /**
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function flushQueue()
    {
        $queueItems = Queue::fromContainer($this->getContainer())->flush();

        /** @var QueueItem $item */
        foreach ($queueItems as $item) {
            $verb = strtolower($item->getCommandObject()::getVerb());

            EventEmitter::fromContainer($this->getContainer())
                ->emit('irc.line.out', [$item, $this->getContainer()]);

            EventEmitter::fromContainer($this->getContainer())
                ->emit('irc.line.out.' . $verb, [$item, $this->getContainer()]);

            if (!$item->isCancelled()) {
                $this->write($item->getCommandObject());
            }
        }
    }

    /**
     * @param string $data
     *
     * @return \React\Promise\PromiseInterface
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function write(string $data)
    {
        if (substr_count($data, "\r") > 1 || substr_count($data, "\n") > 1) {
            $pieces = explode("\r", str_replace("\n", "\r", $data));

            Logger::fromContainer($this->getContainer())
                ->warning('Multiline message caught! Only sending first line. Please file a bug report, this should not happen.',
                    [
                        'pieces' => $pieces
                    ]);

            $data = $pieces[0] . "\r\n";
        }

        $promise = $this->connectorPromise->then(function (ConnectionInterface $stream) use ($data) {
            EventEmitter::fromContainer($this->getContainer())
                ->emit('stream.data.out', [$data]);

            Logger::fromContainer($this->getContainer())
                ->debug('>> ' . $data);
            $stream->write($data);
        });

        return $promise;
    }

    /**
     * @param ISupport $incomingIrcMessage
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function handleServerConfig(ISupport $incomingIrcMessage)
    {
        $hostname = $incomingIrcMessage->getServer();
        Configuration::fromContainer($this->getContainer())['serverConfig']['hostname'] = $hostname;

        // The first argument is the nickname set.
        $currentNickname = $incomingIrcMessage->getNickname();
        Configuration::fromContainer($this->getContainer())['currentNickname'] = $currentNickname;

        Logger::fromContainer($this->getContainer())
            ->debug('Set current nickname to configuration key currentNickname', [$currentNickname]);

        $variables = $incomingIrcMessage->getVariables();
        $currentSettings = Configuration::fromContainer($this->getContainer())['serverConfig'] ?? [];
        Configuration::fromContainer($this->getContainer())['serverConfig'] = array_merge($currentSettings, $variables);

        Logger::fromContainer($this->getContainer())
            ->debug('Set new server configuration to configuration serverConfig.',
                [Configuration::fromContainer($this->getContainer())['serverConfig']]);

        EventEmitter::fromContainer($this->getContainer())->emit(
            'irc.config.updated',
            [Configuration::fromContainer($this->getContainer())['serverConfig']]
        );
    }

    /**
     * @param ConnectorInterface $connectorInterface
     *
     * @return Promise|\React\Promise\PromiseInterface
     */
    public function connect(ConnectorInterface $connectorInterface)
    {
        $connectionString = $this->getConnectionDetails()->getAddress() . ':' . $this->getConnectionDetails()->getPort();
        $promise = $connectorInterface->connect($connectionString)
            ->then(function (ConnectionInterface $connectionInterface) use (&$buffer, $connectionString) {
                EventEmitter::fromContainer($this->getContainer())
                    ->emit('stream.created', [Queue::fromContainer($this->getContainer())]);

                $connectionInterface->on('error',
                    function ($error) use ($connectionString) {
                        throw new ConnectionException('Connection to ' . $connectionString . ' failed: ' . $error);
                    });

                $connectionInterface->on('data',
                    function ($data) {
                        EventEmitter::fromContainer($this->getContainer())
                            ->emit('stream.data.in', [$data]);
                    });

                return $connectionInterface;
            });

        $this->connectorPromise = $promise;
        return $promise;
    }

    /**
     * @return \React\Promise\PromiseInterface
     */
    public function close()
    {
        $promise = $this->connectorPromise->then(function (ConnectionInterface $stream) {
            Logger::fromContainer($this->getContainer())
                ->warning('Closing connection...');
            $stream->close();
            EventEmitter::fromContainer($this->getContainer())
                ->emit('stream.closed');
        });

        return $promise;
    }
}