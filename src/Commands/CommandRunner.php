<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Commands;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Commands\CommandParser;
use WildPHP\Commands\CommandProcessor;
use WildPHP\Commands\Exceptions\CommandNotFoundException;
use WildPHP\Commands\Exceptions\InvalidParameterCountException;
use WildPHP\Commands\Exceptions\NoApplicableStrategiesException;
use WildPHP\Commands\Exceptions\ParseException;
use WildPHP\Commands\Exceptions\ValidationException;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\CommandEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Privmsg;

class CommandRunner
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandProcessor
     */
    private $commandProcessor;
    /**
     * @var IrcMessageQueue
     */
    private $queue;
    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;
    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * CommandRunner constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param CommandProcessor $commandProcessor
     * @param LoggerInterface $logger
     * @param IrcMessageQueue $queue
     * @param IrcChannelStorageInterface $channelStorage
     * @param IrcUserStorageInterface $userStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        CommandProcessor $commandProcessor,
        LoggerInterface $logger,
        IrcMessageQueue $queue,
        IrcChannelStorageInterface $channelStorage,
        IrcUserStorageInterface $userStorage
    ) {
        $eventEmitter->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);

        $this->eventEmitter = $eventEmitter;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->commandProcessor = $commandProcessor;
        $this->queue = $queue;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
    }

    /**
     * @param PRIVMSG $privmsg
     *
     * @throws ValidationException
     */
    public function parseAndRunCommand(Privmsg $privmsg): void
    {
        $prefix = $this->configuration['prefix'];

        $message = $privmsg->getMessage();

        try {
            $parsed = CommandParser::parseFromString($message, $prefix);

            // TODO: Fix this workaround.
            $parameters = $parsed->getArguments();
            $command = $this->commandProcessor->findCommand($parsed->getCommand());
            $strategy = CommandParser::findApplicableStrategy($command, $parameters);
            $parsed->setArguments(
                $strategy->remapNumericParameterIndexes($parameters)
            );

            $processed = $this->commandProcessor->process($parsed);
        } catch (CommandNotFoundException | ParseException $e) {
            $this->logger->debug('Message not a command');
            return;
        } catch (NoApplicableStrategiesException | InvalidParameterCountException $e) {
            $this->logger->debug('No valid strategies found.');
            $this->queue->privmsg($privmsg->getChannel(), 'Invalid arguments.');

            return;
        }

        $channel = $this->channelStorage->getOneByName($privmsg->getChannel());
        $user = $this->userStorage->getOneByNickname($privmsg->getNickname());

        $event = new CommandEvent(
            $processed->getCommand(),
            $channel,
            $user,
            $processed->getArguments()
        );

        $this->eventEmitter->emit('irc.command', [$event]);
        call_user_func($processed->getCallback(), $event);
    }
}
