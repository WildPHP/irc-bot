<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Messages\RPL\ISupport;

class ServerConfigObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * ServerConfigUpdater constructor.
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param Configuration $configuration
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        Configuration $configuration
    ) {
        $eventEmitter->on('irc.msg.in.005', [$this, 'updateServerInformation']);
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @param IncomingIrcMessageEvent $event
     */
    public function updateServerInformation(IncomingIrcMessageEvent $event): void
    {
        /** @var ISupport $message */
        $message = $event->getIncomingMessage();
        $hostname = $message->getServer();
        $this->configuration['serverConfig']['hostname'] = $hostname;

        // The first argument is the nickname set.
        $currentNickname = $message->getNickname();
        $this->configuration['currentNickname'] = $currentNickname;

        $this->logger->debug('Set current nickname to configuration key currentNickname', [$currentNickname]);

        $variables = $message->getVariables();
        $currentSettings = $this->configuration['serverConfig'] ?? [];
        $this->configuration['serverConfig'] = array_merge($currentSettings, $variables);

        $this->logger->debug(
            'Set new server configuration to configuration serverConfig.',
            [$this->configuration['serverConfig']]
        );

        $this->eventEmitter->emit(
            'irc.config.updated',
            [$this->configuration['serverConfig']]
        );
    }
}
