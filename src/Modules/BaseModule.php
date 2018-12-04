<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Modules;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ContainerTrait;
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
}