<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

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
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelNotFoundException;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Core\Database\Database;
use WildPHP\Core\StateException;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserNotFoundException;
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
     * @var Database
     */
    private $database;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandProcessor
     */
    private $commandProcessor;

    /**
     * CommandRunner constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param CommandProcessor $commandProcessor
     * @param Database $database
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        CommandProcessor $commandProcessor,
        Database $database,
        LoggerInterface $logger
    ) {
        $eventEmitter->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);

        $this->eventEmitter = $eventEmitter;
        $this->configuration = $configuration;
        $this->database = $database;
        $this->logger = $logger;
        $this->commandProcessor = $commandProcessor;
    }

    /**
     * @param PRIVMSG $privmsg
     * @param QueueInterface $queue
     * @throws ChannelNotFoundException
     * @throws StateException
     * @throws UserNotFoundException
     * @throws ValidationException
     */
    public function parseAndRunCommand(PRIVMSG $privmsg, QueueInterface $queue)
    {
        $prefix = $this->configuration['prefix'];
        $commandProcessor = $this->commandProcessor;

        $db = $this->database;

        $channel = Channel::fromDatabase($db, ['name' => $privmsg->getChannel()]);
        $user = User::fromDatabase($db, ['nickname' => $privmsg->getNickname()]);

        $message = $privmsg->getMessage();

        try {
            $parsedMessage = CommandParser::parseFromString($message, $prefix);
            $processedCommand = $commandProcessor->process($parsedMessage);
        } catch (CommandNotFoundException | ParseException $e) {
            $this->logger->debug("Message not a command");
            return;
        } catch (NoApplicableStrategiesException | InvalidParameterCountException $e) {
            $this->logger->debug('No valid strategies found.');
            $queue->privmsg($channel->getName(),
                'Invalid arguments. Please check ' . $prefix . 'cmdhelp ' . $parsedMessage->getCommand() . ' for usage instructions and make sure that your ' .
                'parameters match the given requirements.');

            return;
        }

        $this->eventEmitter->emit('irc.command', [
                $processedCommand->getCommand(),
                $channel,
                $user,
                $processedCommand->getArguments()
            ]);

        call_user_func(
            $processedCommand->getCallback(),
            $channel,
            $user,
            $processedCommand->getConvertedParameters(),
            $processedCommand->getCommand()
        );
    }
}