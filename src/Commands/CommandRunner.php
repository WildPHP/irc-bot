<?php
/*
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
use WildPHP\Commands\ProcessedCommand;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
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
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;
    
    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * CommandRunner constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param CommandProcessor $commandProcessor
     * @param LoggerInterface $logger
     * @param IrcChannelStorageInterface $channelStorage
     * @param IrcUserStorageInterface $userStorage
     * @param IrcMessageQueue $queue
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        CommandProcessor $commandProcessor,
        LoggerInterface $logger,
        IrcChannelStorageInterface $channelStorage,
        IrcUserStorageInterface $userStorage,
        IrcMessageQueue $queue
    ) {
        $eventEmitter->on('irc.line.in.privmsg', [$this, 'processPrivmsg']);

        $this->eventEmitter = $eventEmitter;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->commandProcessor = $commandProcessor;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->queue = $queue;
    }

    /**
     * @param Privmsg $privmsg
     */
    public function processPrivmsg(Privmsg $privmsg): void
    {
        try {
            $processed = $this->processCommandLine($privmsg->getMessage(), $this->configuration['prefix']);
        }

        // Do not process messages without command or malformed ones.
        catch (CommandNotFoundException | ParseException $e) {
            $this->logger->debug('[CommandRunner] Dropping message without command.');
            return;
        }

        // When a validation error occurs or parameters do not match, send a message to the user.
        catch (InvalidParameterCountException | NoApplicableStrategiesException | ValidationException $e) {
            $this->logger->debug('[CommandRunner] Dropping message with malformed parameters.');
            $this->queue->privmsg($privmsg->getChannel(), 'Invalid parameters.');
            return;
        }

        $this->runProcessedCommand(
            $processed,
            $this->userStorage->getOneByNickname($privmsg->getNickname()),
            $this->channelStorage->getOneByName($privmsg->getChannel())
        );
    }

    /**
     * @param ProcessedCommand $processed
     * @param IrcUser $user
     * @param IrcChannel $channel
     */
    public function runProcessedCommand(ProcessedCommand $processed, IrcUser $user, IrcChannel $channel): void
    {
        $event = new CommandEvent(
            $processed->getCommand(),
            $channel,
            $user,
            $processed->getArguments()
        );

        $this->eventEmitter->emit('irc.command', [$event]);
        call_user_func($processed->getCallback(), $event);
    }

    /**
     * @param string $line
     * @param string $prefix
     * @return ProcessedCommand
     *
     * @throws CommandNotFoundException
     * @throws InvalidParameterCountException
     * @throws NoApplicableStrategiesException
     * @throws ParseException
     * @throws ValidationException
     */
    public function processCommandLine(string $line, string $prefix): ProcessedCommand
    {
        $parsed = CommandParser::parseFromString($line, $prefix);

        // TODO: Fix this workaround that fixes parameter indexes.
        $parameters = $parsed->getArguments();
        $command = $this->commandProcessor->findCommand($parsed->getCommand());
        $strategy = CommandParser::findApplicableStrategy($command, $parameters);
        $parsed->setArguments(
            $strategy->remapNumericParameterIndexes($parameters)
        );

        return $this->commandProcessor->process($parsed);
    }
}
