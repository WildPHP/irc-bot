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
use WildPHP\Core\Configuration\Configuration;
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
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger, Configuration $configuration)
    {
        $eventEmitter->on('irc.line.in.005', [$this, 'updateServerInformation']);
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @param ISupport $incomingIrcMessage
     */
    public function updateServerInformation(ISupport $incomingIrcMessage): void
    {
        $hostname = $incomingIrcMessage->getServer();
        $this->configuration['serverConfig']['hostname'] = $hostname;

        // The first argument is the nickname set.
        $currentNickname = $incomingIrcMessage->getNickname();
        $this->configuration['currentNickname'] = $currentNickname;

        $this->logger->debug('Set current nickname to configuration key currentNickname', [$currentNickname]);

        $variables = $incomingIrcMessage->getVariables();
        $currentSettings = $this->configuration['serverConfig'] ?? [];
        $this->configuration['serverConfig'] = array_merge($currentSettings, $variables);

        $this->logger->debug('Set new server configuration to configuration serverConfig.',
                [$this->configuration['serverConfig']]);

        $this->eventEmitter->emit(
            'irc.config.updated',
            [$this->configuration['serverConfig']]
        );
    }
}