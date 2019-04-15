<?php
declare(strict_types=1);

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Management;

use WildPHP\Commands\Command;
use WildPHP\Commands\Parameters\StringParameter;
use WildPHP\Commands\ParameterStrategy;
use WildPHP\Core\Commands\CommandRegistrar;
use WildPHP\Core\Commands\Parameters\ChannelParameter;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\CommandEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorageInterface;

class ManagementCommands
{
    /**
     * @var IrcMessageQueue
     */
    private $queue;
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * ManagementCommands constructor.
     *
     * @param CommandRegistrar $registrar
     * @param IrcMessageQueue $queue
     * @param Configuration $configuration
     * @param IrcChannelStorageInterface $channelStorage
     */
    public function __construct(
        CommandRegistrar $registrar,
        IrcMessageQueue $queue,
        Configuration $configuration,
        IrcChannelStorageInterface $channelStorage
    ) {
        $registrar->register(
            'join',
            new Command(
                [$this, 'joinCommand'],
                new ParameterStrategy(1, 5, [
                    'channel1' => new StringParameter(),
                    'channel2' => new StringParameter(),
                    'channel3' => new StringParameter(),
                    'channel4' => new StringParameter(),
                    'channel5' => new StringParameter()
                ])
            )
        );

        $registrar->register(
            'part',
            new Command(
                [$this, 'partCommand'],
                new ParameterStrategy(0, 5, [
                    'channel1' => new ChannelParameter($channelStorage),
                    'channel2' => new ChannelParameter($channelStorage),
                    'channel3' => new ChannelParameter($channelStorage),
                    'channel4' => new ChannelParameter($channelStorage),
                    'channel5' => new ChannelParameter($channelStorage)
                ])
            )
        );

        $registrar->register(
            'quit',
            new Command(
                [$this, 'quitCommand'],
                new ParameterStrategy(0, -1, [
                    'message' => new StringParameter()
                ], true)
            )
        );

        $registrar->register(
            'nick',
            new Command(
                [$this, 'nickCommand'],
                new ParameterStrategy(0, -1, [
                    'newNickname' => new StringParameter()
                ])
            )
        );

        $registrar->register(
            'clearqueue',
            new Command(
                [$this, 'clearQueueCommand'],
                new ParameterStrategy(0, 0)
            )
        );

        $this->queue = $queue;
        $this->configuration = $configuration;
    }

    /**
     * @param CommandEvent $event
     */
    public function quitCommand(CommandEvent $event): void
    {
        $message = $event->getParameters()['message'] ?? 'Quit command given by ' . $event->getUser()->getNickname();
        $this->queue->quit($message);
    }

    /**
     * @param CommandEvent $event
     */
    public function joinCommand(CommandEvent $event): void
    {
        $channels = $event->getParameters();

        $validChannels = $this->validateChannels($channels);

        if (!empty($validChannels)) {
            $this->queue
                ->join($validChannels);
        }

        $diff = array_diff($channels, $validChannels);

        if (!empty($diff)) {
            $this->queue->privmsg(
                $event->getUser()->getNickname(),
                'Did not join the following channels because they do not follow proper formatting: ' . implode(
                    ', ',
                    $diff
                )
            );
        }
    }

    /**
     * @param array $channels
     *
     * @return array
     */
    protected function validateChannels(array $channels): array
    {
        $validChannels = [];
        $serverChannelPrefix = $this->configuration['serverConfig']['chantypes'];

        foreach ($channels as $channel) {
            if (strpos($channel, $serverChannelPrefix) !== 0) {
                continue;
            }

            $validChannels[] = $channel;
        }

        return $validChannels;
    }

    /**
     * @param CommandEvent $event
     */
    public function partCommand(CommandEvent $event): void
    {
        if (empty($event->getParameters()['channels'])) {
            $channels = [$event->getChannel()];
        } else {
            $channels = $event->getParameters()['channels'];
        }

        foreach ($channels as $index => $channel) {
            $channels[$index] = $channel->getName();
        }

        $validChannels = $this->validateChannels($channels);

        if (!empty($validChannels)) {
            $this->queue
                ->part($validChannels);
        }

        $diff = array_diff($channels, $validChannels);

        if (!empty($diff)) {
            $this->queue
                ->privmsg(
                    $event->getUser()->getNickname(),
                    'Did not part the following channels because they do not follow proper formatting: ' . implode(
                        ', ',
                        $diff
                    )
                );
        }
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param CommandEvent $event
     */
    public function nickCommand(CommandEvent $event): void
    {
        // TODO: Validate
        $this->queue->nick($event->getParameters()['newNickname']);
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param CommandEvent $event
     */
    public function clearQueueCommand(CommandEvent $event): void
    {
        $this->queue->clear();
        $this->queue->privmsg(
            $event->getChannel()->getName(),
            $event->getUser()->getNickname() . ': Message queue cleared.'
        );
    }
}
