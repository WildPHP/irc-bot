<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

use ValidationClosures\Types;
use ValidationClosures\Utils;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Database\Database;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Permissions\Validator;
use WildPHP\Core\StateException;
use WildPHP\Core\Users\User;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class CommandHandler extends BaseModule implements ComponentInterface
{
    use ComponentTrait;
    use ContainerTrait;

    /**
     * @var Collection
     */
    protected $commandCollection = null;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * CommandHandler constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\ContainerException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        // TODO: Fix this into a nicer solution
        $container->add($this);
        $this->setCommandCollection(new Collection(Types::instanceof(Command::class)));

        EventEmitter::fromContainer($container)
            ->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);
        $this->setContainer($container);
    }

    /**
     * @param string $command
     * @param Command $commandObject
     * @param string[] $aliases
     *
     * @return bool
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function registerCommand(string $command, Command $commandObject, array $aliases = [])
    {
        if ($this->getCommandCollection()->offsetExists($command) || !Utils::validateArray(Types::string(), $aliases)) {
            return false;
        }

        $this->getCommandCollection()->offsetSet($command, $commandObject);

        Logger::fromContainer($this->getContainer())
            ->debug(
                'New command registered',
                ['command' => $command]
            );

        foreach ($aliases as $alias) {
            $this->alias($command, $alias);
        }

        return true;
    }

    /**
     * @param string $originalCommand
     * @param string $alias
     *
     * @return bool
     */
    public function alias(string $originalCommand, string $alias): bool
    {
        if (!$this->getCommandCollection()->offsetExists($originalCommand) || array_key_exists($alias,
                $this->aliases)) {
            return false;
        }

        /** @var Command $commandObject */
        $commandObject = $this->getCommandCollection()[$originalCommand];
        $this->aliases[$alias] = $commandObject;
        return true;
    }

    /**
     * @param PRIVMSG $privmsg
     * @param Queue $queue
     * @throws StateException
     * @throws \WildPHP\Core\Channels\ChannelNotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \WildPHP\Core\Users\UserNotFoundException
     */
    public function parseAndRunCommand(PRIVMSG $privmsg, Queue $queue)
    {
        $db = Database::fromContainer($this->getContainer());

        $channel = Channel::fromDatabase($db, ['name' => $privmsg->getChannel()]);
        $user = User::fromDatabase($db, ['nickname' => $privmsg->getNickname()]);

        $message = $privmsg->getMessage();

        $args = [];
        $command = $this->parseCommandFromMessage($message, $args);

        if ($command === false) {
            return;
        }

        EventEmitter::fromContainer($this->getContainer())
            ->emit('irc.command', [$command, $channel, $user, $args, $this->getContainer()]);

        $commandObject = $this->findCommandInDictionary($command);

        if (!$commandObject) {
            return;
        }

        $permission = $commandObject->getRequiredPermission();
        if ($permission && !Validator::fromContainer($this->getContainer())->isAllowedTo($permission, $user,
                $channel)) {
            $queue->privmsg($channel->getName(),
                $user->getNickname() . ': You do not have the required permission to run this command (' . $permission . ')');

            return;
        }

        $strategy = $this->findApplicableStrategy($commandObject, $args);

        if (!$strategy) {
            Logger::fromContainer($this->getContainer())->debug('No valid strategies found.');
            $prefix = Configuration::fromContainer($this->getContainer())['prefix'];
            $queue->privmsg($channel->getName(),
                'Invalid arguments. Please check ' . $prefix . 'cmdhelp ' . $command . ' for usage instructions and make sure that your ' .
                'parameters match the given requirements.');

            return;
        }

        call_user_func($commandObject->getCallback(), $channel, $user, $args, $this->getContainer(), $command);
    }

    /**
     * @param string $command
     *
     * @return Command|null
     */
    protected function findCommandInDictionary(string $command): ?Command
    {
        $dictionary = $this->getCommandCollection();

        if (!$dictionary->offsetExists($command) && !array_key_exists($command, $this->aliases)) {
            return null;
        }

        /** @var Command $commandObject */
        return $dictionary[$command] ?? $this->aliases[$command];
    }

    /**
     * @param Command $commandObject
     * @param array $args
     *
     * @return null|ParameterStrategy
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Exception
     */
    protected function findApplicableStrategy(Command $commandObject, array &$args): ?ParameterStrategy
    {
        $parameterStrategies = $commandObject->getParameterStrategies();
        $strategy = null;
        $originalArgs = $args;

        /** @var ParameterStrategy $parameterStrategy */
        foreach ($parameterStrategies as $parameterStrategy) {
            try {
                $args = $parameterStrategy->validateArgumentArray($originalArgs);
                $strategy = $parameterStrategy;
                break;
            } catch (\InvalidArgumentException $e) {
                Logger::fromContainer($this->getContainer())->debug('Not applying strategy; ' . $e->getMessage());
            }
        }

        return $strategy;
    }

    /**
     * @param string $message
     * @param array $args
     *
     * @return false|string
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function parseCommandFromMessage(string $message, array &$args)
    {
        $messageParts = explode(' ', trim($message));
        $firstPart = array_shift($messageParts);
        $prefix = Configuration::fromContainer($this->getContainer())['prefix'];

        if (strlen($firstPart) == strlen($prefix)) {
            return false;
        }

        if (substr($firstPart, 0, strlen($prefix)) != $prefix) {
            return false;
        }

        $command = substr($firstPart, strlen($prefix));

        // Remove empty elements and excessive spaces.
        $args = array_values(array_map('trim', array_filter($messageParts, function ($arg) {
            return !preg_match('/^$|\s/', $arg);
        })));

        return $command;
    }

    /**
     * @return Collection
     */
    public function getCommandCollection(): Collection
    {
        return $this->commandCollection;
    }

    /**
     * @param Collection $commandCollection
     */
    public function setCommandCollection(Collection $commandCollection)
    {
        $this->commandCollection = $commandCollection;
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}