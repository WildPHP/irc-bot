<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

use WildPHP\Commands\CommandParser;
use WildPHP\Commands\Exceptions\CommandNotFoundException;
use WildPHP\Commands\Exceptions\InvalidParameterCountException;
use WildPHP\Commands\Exceptions\NoApplicableStrategiesException;
use WildPHP\Commands\Exceptions\ParseException;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelNotFoundException;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Database\Database;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\StateException;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserNotFoundException;
use WildPHP\Messages\Privmsg;

class CommandRunner extends BaseModule
{
    use ContainerTrait;

    /**
     * CommandRunner constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        EventEmitter::fromContainer($container)
            ->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);
        $this->setContainer($container);
    }

    /**
     * @param PRIVMSG $privmsg
     * @param Queue $queue
     * @throws StateException
     * @throws \WildPHP\Commands\Exceptions\ValidationException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function parseAndRunCommand(PRIVMSG $privmsg, Queue $queue)
    {
        $prefix = Configuration::fromContainer($this->getContainer())['prefix'];
        $commandProcessor = CommandRegistrar::fromContainer($this->getContainer())->getProcessor();

        $db = Database::fromContainer($this->getContainer());

        try {
            $channel = Channel::fromDatabase($db, ['name' => $privmsg->getChannel()]);
            $user = User::fromDatabase($db, ['nickname' => $privmsg->getNickname()]);
        } catch (ChannelNotFoundException | UserNotFoundException $e) {
            Logger::fromContainer($this->getContainer())->warn("State mismatch");
            return;
        }

        $message = $privmsg->getMessage();

        try {
            $parsedMessage = CommandParser::parseFromString($message, $prefix);
            $processedCommand = $commandProcessor->process($parsedMessage);
        } catch (CommandNotFoundException | ParseException $e) {
            Logger::fromContainer($this->getContainer())->debug("Message not a command");
            return;
        } catch (NoApplicableStrategiesException | InvalidParameterCountException $e) {
            Logger::fromContainer($this->getContainer())->debug('No valid strategies found.');
            $queue->privmsg($channel->getName(),
                'Invalid arguments. Please check ' . $prefix . 'cmdhelp ' . $parsedMessage->getCommand() . ' for usage instructions and make sure that your ' .
                'parameters match the given requirements.');

            return;
        }

        EventEmitter::fromContainer($this->getContainer())
            ->emit('irc.command', [
                $processedCommand->getCommand(),
                $channel,
                $user,
                $processedCommand->getArguments(),
                $this->getContainer()
            ]);

        call_user_func(
            $processedCommand->getCallback(),
            $channel,
            $user,
            $processedCommand->getConvertedParameters(),
            $this->getContainer(),
            $processedCommand->getCommand()
        );
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }

    /**
     * @return array
     */
    public static function getDependentModules(): array
    {
        return [
            Configuration::class,
            CommandRegistrar::class,
            EventEmitter::class,
            Logger::class
        ];
    }
}