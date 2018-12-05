<?php

/**
 * Copyright 2018 The WildPHP Team
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
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Permissions\AllowedBy;
use WildPHP\Core\Permissions\Validator;

class ManagementCommands
{
    /**
     * @var QueueInterface
     */
    private $queue;
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var Validator
     */
    private $validator;

    /**
     * ManagementCommands constructor.
     *
     * @param CommandRegistrar $registrar
     * @param QueueInterface $queue
     * @param Configuration $configuration
     * @param Validator $validator
     */
    public function __construct(CommandRegistrar $registrar, QueueInterface $queue, Configuration $configuration, Validator $validator)
    {
        $registrar->register('join',
            new Command(
                [$this, 'joinCommand'],
                new ParameterStrategy(1, 5, [
                    'channel1' => new StringParameter(),
                    'channel2' => new StringParameter(),
                    'channel3' => new StringParameter(),
                    'channel4' => new StringParameter(),
                    'channel5' => new StringParameter()
                ])
            ));

        $registrar->register('part',
            new Command(
                [$this, 'partCommand'],
                new ParameterStrategy(0, 5, [
                    'channel1' => new ChannelParameter(),
                    'channel2' => new ChannelParameter(),
                    'channel3' => new ChannelParameter(),
                    'channel4' => new ChannelParameter(),
                    'channel5' => new ChannelParameter()
                ])
            ));

        $registrar->register('quit',
            new Command(
                [$this, 'quitCommand'],
                new ParameterStrategy(0, -1, [
                    'message' => new StringParameter()
                ], true)
            ));

        $registrar->register('nick',
            new Command(
                [$this, 'nickCommand'],
                new ParameterStrategy(0, -1, [
                    'newNickname' => new StringParameter()
                ])
            ));

        $registrar->register('clearqueue',
            new Command(
                [$this, 'clearqueueCommand'],
                new ParameterStrategy(0, 0)
            ));

        $this->queue = $queue;
        $this->configuration = $configuration;
        $this->validator = $validator;
    }

    /**
     * @param IrcChannel $source
     * @param IrcUser $user
     * @param $args
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function quitCommand(IrcChannel $source, IrcUser $user, $args)
    {
        if (!$this->validator->isAllowedTo('quit', $user, $source)) {
            $this->queue->privmsg($source->getName(), sprintf(AllowedBy::DENIED_MESSAGE, 'quit'));
            return;
        }

        $message = implode(' ', $args);

        if (empty($message)) {
            $message = 'Quit command given by ' . $user->getNickname();
        }

        $this->queue
            ->quit($message);
    }

    /**
     * @param IrcChannel $source
     * @param IrcUser $user
     * @param array $channels
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function joinCommand(IrcChannel $source, IrcUser $user, array $channels)
    {
        if (!$this->validator->isAllowedTo('join', $user, $source)) {
            $this->queue->privmsg($source->getName(), sprintf(AllowedBy::DENIED_MESSAGE, 'join'));
            return;
        }

        $validChannels = $this->validateChannels($channels);

        if (!empty($validChannels)) {
            $this->queue
                ->join($validChannels);
        }

        $diff = array_diff($channels, $validChannels);

        if (!empty($diff)) {
            $this->queue
                ->privmsg($user->getNickname(),
                    'Did not join the following channels because they do not follow proper formatting: ' . implode(', ',
                        $diff));
        }
    }

    /** @noinspection PhpUnusedParameterInspection */

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
            if (substr($channel, 0, strlen($serverChannelPrefix)) != $serverChannelPrefix) {
                continue;
            }

            $validChannels[] = $channel;
        }

        return $validChannels;
    }

    /**
     * @param IrcChannel $source
     * @param IrcUser $user
     * @param IrcChannel[] $channels
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function partCommand(IrcChannel $source, IrcUser $user, $channels)
    {
        if (!$this->validator->isAllowedTo('part', $user, $source)) {
            $this->queue->privmsg($source->getName(), sprintf(AllowedBy::DENIED_MESSAGE, 'part'));
            return;
        }

        if (empty($channels)) {
            $channels = [$source];
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
                ->privmsg($user->getNickname(),
                    'Did not part the following channels because they do not follow proper formatting: ' . implode(', ',
                        $diff));
        }
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param IrcChannel $source
     * @param IrcUser $user
     * @param array $args
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function nickCommand(IrcChannel $source, IrcUser $user, array $args)
    {
        if (!$this->validator->isAllowedTo('nick', $user, $source)) {
            $this->queue->privmsg($source->getName(), sprintf(AllowedBy::DENIED_MESSAGE, 'nick'));
            return;
        }

        // TODO: Validate
        $this->queue->nick($args['newNickname']);
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param IrcChannel $source
     * @param IrcUser $user
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function clearqueueCommand(IrcChannel $source, IrcUser $user)
    {
        if (!$this->validator->isAllowedTo('clearqueue', $user, $source)) {
            $this->queue->privmsg($source->getName(), sprintf(AllowedBy::DENIED_MESSAGE, 'clearqueue'));
            return;
        }

        $this->queue->clear();
        $this->queue->privmsg($source->getName(),
            $user->getNickname() . ': Message queue cleared.');
    }
}