<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use React\EventLoop\LoopInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IrcConnectionInitiator;
use WildPHP\Core\Modules\ModuleFactory;
use WildPHP\Core\Connection\IrcConnectionInterface;
use WildPHP\Core\Permissions\Validator;

require('../vendor/autoload.php');
require('../propel/config.php');
define('WPHP_ROOT_DIR', dirname(__DIR__));
define('WPHP_VERSION', '3.0.0');

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/container_configuration.php');
$container = $builder->build();

$configuration = $container->get(Configuration::class);

$coreModules = include(__DIR__ . '/core_modules.php');
$modules = $configuration['modules'] ?? [];
$modules = array_merge($modules, $coreModules);

$moduleFactory = $container->get(ModuleFactory::class);
$moduleFactory->initializeModules($modules);

$ircConnection = $container->get(IrcConnectionInterface::class);
$ircConnectionInitiator = $container->get(IrcConnectionInitiator::class);
$ircConnectionInitiator->startConnection($ircConnection);

$container->get(LoopInterface::class)->run();