<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Commands\Command;
use WildPHP\Commands\ParameterStrategy;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Observers\Queue;

class HelpCommand extends BaseModule
{
    /**
     * HelpCommand constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        /**CommandRegistrar::fromContainer($container)->register('cmdhelp',
         * new Command(
         * [$this, 'helpCommand'],
         * new ParameterStrategy(0, 1, [
         * 'command' => new StringParameter()
         * ])
         * ));*/

        CommandRegistrar::fromContainer($container)->register('lscommands',
            new Command(
                [$this, 'lscommandsCommand'],
                new ParameterStrategy(0, 0)
            ));

        Logger::fromContainer($container)->warn('Cannot currently fully load the Help module since help is not implemented.');
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function lscommandsCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        $commands = CommandRegistrar::fromContainer($container)
            ->getProcessor()->getCommandCollection()->keys();

        $commands = implode(', ', $commands);
        $commands = explode("\n", wordwrap($commands, 200));

        foreach ($commands as $key => $commandList) {
            Queue::fromContainer($container)
                ->privmsg($source->getName(), $user->getNickname() . ': Available commands: ' . $commandList);
        }
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function helpCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        if (empty($args)) {
            $args['command'] = 'cmdhelp';
        }

        $command = $args['command'];

        if (!CommandRegistrar::fromContainer($container)->getProcessor()->getCommandCollection()->offsetExists($command)) {
            Queue::fromContainer($container)
                ->privmsg($source->getName(), 'That command does not exist, sorry!');

            return;
        }

        /** @var Command $commandObject */
        $commandObject = CommandRegistrar::fromContainer($container)
            ->getProcessor()->getCommandCollection()[$command];

        $helpObject = clone $commandObject->getHelp();
        if ($helpObject == null || !($helpObject instanceof CommandHelp)) {
            Queue::fromContainer($container)
                ->privmsg($source->getName(), 'There is no help available for this command.');

            return;
        }

        /*if (!empty($commandObject->getAliasCollection()->getArrayCopy()))
            $helpObject->append('Aliases: ' . implode(', ', $commandObject->getAliasCollection()->getArrayCopy()));*/

        foreach ($helpObject->getIterator() as $page) {
            Queue::fromContainer($container)->privmsg($source->getName(), $command . ': ' . $page);
        }
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}