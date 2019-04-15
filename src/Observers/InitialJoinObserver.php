<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);
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
use WildPHP\Core\Queue\IrcMessageQueue;

class InitialJoinObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * InitialJoinObserver constructor.
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     * @param IrcMessageQueue $queue
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        LoggerInterface $logger,
        IrcMessageQueue $queue
    ) {
        // 001: RPL_WELCOME
        $eventEmitter->on('irc.msg.in.001', [$this, 'joinInitialChannels']);

        $this->logger = $logger;
        $this->queue = $queue;
        $this->configuration = $configuration;
    }

    /**
     * @return void
     */
    public function joinInitialChannels(): void
    {
        $channels = $this->configuration['connection']['channels'];

        if (empty($channels)) {
            return;
        }

        $chunks = array_chunk($channels, 3);

        foreach ($chunks as $chunk) {
            $this->queue->join($chunk);
        }

        $this->logger->debug(
            'Queued initial channel join.',
            [
                'count' => count($channels),
                'channels' => $channels
            ]
        );
    }
}
