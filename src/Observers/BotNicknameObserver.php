<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\NicknameChangedEvent;

class BotNicknameObserver
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BotStateManager constructor.
     *
     * @param Configuration $configuration
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     */
    public function __construct(
        Configuration $configuration,
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger
    ) {
        $eventEmitter->on('user.nick', [$this, 'monitorBotNickname']);

        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @param NicknameChangedEvent $event
     */
    public function monitorBotNickname(NicknameChangedEvent $event): void
    {
        if ($event->getOldNickname() !== $this->configuration['currentNickname']) {
            return;
        }

        $this->configuration['currentNickname'] = $event->getNewNickname();

        $this->logger->debug('Updated current nickname configuration for bot', [
            'oldNickname' => $event->getOldNickname(),
            'newNickname' => $event->getNewNickname()
        ]);
    }
}
