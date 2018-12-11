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
use WildPHP\Core\Connection\IrcConnectionInterface;
use WildPHP\Core\Modules\ModuleFactory;

require('../vendor/autoload.php');
define('WPHP_ROOT_DIR', dirname(__DIR__));
define('WPHP_VERSION', '3.0.0');

$configuration = require_once(WPHP_ROOT_DIR . '/config/config.php');

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/container_configuration.php');
$container = $builder->build();

$configuration = $container->get(Configuration::class);
$container->set(\WildPHP\Core\Storage\Providers\DatabaseStorageProviderInterface::class,
    $configuration['storage']['provider']);

$coreModules = include(__DIR__ . '/core_modules.php');
$modules = $configuration['modules'] ?? [];
$modules = array_merge($modules, $coreModules);

$moduleFactory = $container->get(ModuleFactory::class);
$moduleFactory->initializeModules($modules);

$ircConnection = $container->get(IrcConnectionInterface::class);
$ircConnectionInitiator = $container->get(IrcConnectionInitiator::class);
$ircConnectionInitiator->startConnection($ircConnection);

$container->get(LoopInterface::class)->run();