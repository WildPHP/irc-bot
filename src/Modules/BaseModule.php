<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Modules;

use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Users\User;
use Yoshi2889\Container\ComponentTrait;

abstract class BaseModule implements ModuleInterface
{
    use ComponentTrait;
    use ContainerTrait;

    /**
     * BaseModule constructor.
     *
     * @param ComponentContainer $container
     */
    abstract public function __construct(ComponentContainer $container);

    /**
     * @return array
     */
    abstract public static function getDependentModules(): array;

    /**
     * @param array $checks
     * @param Channel $source
     * @param User $user
     *
     * @return bool
     * @throws \Yoshi2889\Container\NotFoundException
     */
    protected function doChecks(array $checks, Channel $source, User $user)
    {
        foreach ($checks as $string => $check) {
            if (!$check) {
                continue;
            }

            Queue::fromContainer($this->getContainer())
                ->privmsg($source->getName(), $user->getNickname() . ': ' . $string);

            return false;
        }

        return true;
    }
}