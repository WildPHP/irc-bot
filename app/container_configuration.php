<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

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
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorage;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorage;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorage;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Core\Storage\Providers\StorageProviderInterface;
use WildPHP\Queue\QueueProcessor;
use function DI\autowire;
use function DI\create;

define('WPHP_ROOT_DIR', dirname(__DIR__));
define('WPHP_VERSION', '3.0.0');

return [
    EventEmitterInterface::class => create(EventEmitter::class),

    StorageProviderInterface::class => static function (Configuration $configuration) {
        return $configuration['storage']['provider'];
    },

    IrcUserStorageInterface::class => autowire(IrcUserStorage::class),
    IrcChannelStorageInterface::class => autowire(IrcChannelStorage::class),
    IrcUserChannelRelationStorageInterface::class => autowire(IrcUserChannelRelationStorage::class),

    LoggerInterface::class => static function () {
        return new Logger('wildphp', [
            new StreamHandler('php://stdout'),
            new RotatingFileHandler(WPHP_ROOT_DIR . '/logs/log.log')
        ]);
    },

    LoopInterface::class => static function () {
        return Factory::create();
    },

    Configuration::class => static function (LoggerInterface $logger) {
        $file = WPHP_ROOT_DIR . '/config/config.php';
        $logger->info('Reading configuration file ' . $file);
        $phpBackend = new PhpBackend($file);

        $configuration = new Configuration($phpBackend);
        $configuration['directories'] = [
            'root' => WPHP_ROOT_DIR,
            'config' => WPHP_ROOT_DIR . '/config'
        ];
        $configuration['version'] = WPHP_VERSION;

        return $configuration;
    },

    QueueProcessor::class => static function (LoopInterface $loop, IrcMessageQueue $ircMessageQueue) {
        return new QueueProcessor($ircMessageQueue, $loop);
    },

    IrcConnectionInterface::class => static function (
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        ContainerInterface $container
    ) {
        $configuration = $container->get(Configuration::class);
        $connectionDetails = ConnectionDetails::fromArray($configuration['connection']);
        $logger->info('Creating connection', [
            'server' => $connectionDetails->getAddress(),
            'port' => $connectionDetails->getPort()
        ]);

        return new IrcConnection($eventEmitter, $logger, $connectionDetails);
    }
];
