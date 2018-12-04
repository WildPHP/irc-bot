<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Commands\Command;
use WildPHP\Commands\CommandProcessor;

class CommandRegistrar
{
    /**
     * @var CommandProcessor
     */
    private $processor;

    /**
     * CommandRegistrar constructor.
     * @param CommandProcessor $processor
     */
    public function __construct(CommandProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param string $command
     * @param Command $commandObject
     * @return bool
     */
    public function register(string $command, Command $commandObject): bool
    {
        return $this->processor->registerCommand($command, $commandObject);
    }

    /**
     * @return CommandProcessor
     */
    public function getProcessor(): CommandProcessor
    {
        return $this->processor;
    }
}