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
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Entities\IrcChannelQuery;
use WildPHP\Core\Entities\IrcUserQuery;
use WildPHP\Core\Connection\QueueInterface;
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
     * @var QueueInterface
     */
    private $queue;

    /**
     * CommandRunner constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Configuration $configuration
     * @param CommandProcessor $commandProcessor
     * @param LoggerInterface $logger
     * @param QueueInterface $queue
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        Configuration $configuration,
        CommandProcessor $commandProcessor,
        LoggerInterface $logger,
        QueueInterface $queue
    ) {
        $eventEmitter->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);

        $this->eventEmitter = $eventEmitter;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->commandProcessor = $commandProcessor;
        $this->queue = $queue;
    }

    /**
     * @param PRIVMSG $privmsg
     * @throws ValidationException
     */
    public function parseAndRunCommand(PRIVMSG $privmsg)
    {
        $prefix = $this->configuration['prefix'];
        $commandProcessor = $this->commandProcessor;

        $message = $privmsg->getMessage();

        try {
            $parsedMessage = CommandParser::parseFromString($message, $prefix);

            // TODO: Fix this workaround.
            $parameters = $parsedMessage->getArguments();
            $command = $commandProcessor->findCommand($parsedMessage->getCommand());
            $strategy = CommandParser::findApplicableStrategy($command, $parameters);
            $parsedMessage->setArguments($strategy->remapNumericParameterIndexes($parameters));

            $processedCommand = $commandProcessor->process($parsedMessage);
        } catch (CommandNotFoundException | ParseException $e) {
            $this->logger->debug("Message not a command");
            return;
        } catch (NoApplicableStrategiesException | InvalidParameterCountException $e) {
            $this->logger->debug('No valid strategies found.');
            $this->queue->privmsg($privmsg->getChannel(),
                'Invalid arguments. Please check ' . $prefix . 'cmdhelp ' . $parsedMessage->getCommand() . ' for usage instructions and make sure that your ' .
                'parameters match the given requirements.');

            return;
        }

        $channel = IrcChannelQuery::create()->findOneByName($privmsg->getChannel());
        $user = IrcUserQuery::create()->findOneByNickname($privmsg->getNickname());

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