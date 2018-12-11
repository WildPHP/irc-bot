<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use Evenement\EventEmitterInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\PhpBackend;
use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\IrcConnectionInterface;
use WildPHP\Core\Events\EventEmitter;
use function DI\create;

return [
    EventEmitterInterface::class => create(EventEmitter::class),

    LoggerInterface::class => function () {
        $logger = new Logger('wildphp');
        $logger->pushHandler(new StreamHandler('php://stdout'));
        $logger->pushHandler(new RotatingFileHandler(WPHP_ROOT_DIR . '/logs/log.log'));
        return $logger;
    },

    LoopInterface::class => function () {
        return Factory::create();
    },

    \WildPHP\Core\Permissions\Validator::class => function (EventEmitterInterface $eventEmitter, Configuration $configuration) {
        return new \WildPHP\Core\Permissions\Validator($eventEmitter, $configuration['owner']);
    },

    Configuration::class => function (LoggerInterface $logger) {
        $file = WPHP_ROOT_DIR . '/config/config.php';
        $logger->info('Reading configuration file ' . $file);
        $phpBackend = new PhpBackend($file);

        $configuration = new Configuration($phpBackend);
        $configuration['directories'] = [
            'root' => WPHP_ROOT_DIR,
            'config' => WPHP_ROOT_DIR . '/config',
            'storage' => WPHP_ROOT_DIR . '/storage',
        ];
        $configuration['version'] = WPHP_VERSION;

        return $configuration;
    },

    IrcConnectionInterface::class => function (EventEmitterInterface $eventEmitter, LoggerInterface $logger, ContainerInterface $container) {
        $configuration = $container->get(Configuration::class);
        $connectionDetails = ConnectionDetails::fromConfiguration($configuration);
        $logger->info('Creating connection', [
            'server' => $connectionDetails->getAddress(),
            'port' => $connectionDetails->getPort()
        ]);

        return new IrcConnection($eventEmitter, $logger, $connectionDetails);
    },
];