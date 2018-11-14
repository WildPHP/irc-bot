<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use React\EventLoop\Factory as LoopFactory;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Commands\CommandRunner;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Capabilities\CapabilityHandler;
use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Connection\ConnectorFactory;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\DataStorage\DataStorageFactory;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\ModuleFactory;
use WildPHP\Core\Permissions\PermissionGroup;

/**
 * @return Logger
 * @throws Exception
 */
function setupLogger(): Logger
{
    $logger = new Logger('wildphp');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout'));
    $logger->pushHandler(new \Monolog\Handler\RotatingFileHandler(WPHP_ROOT_DIR . '/logs/log.log'));
    return $logger;
}

/**
 * @return Configuration
 */
function setupConfiguration()
{
    $neonBackend = new \WildPHP\Core\Configuration\NeonBackend(WPHP_ROOT_DIR . '/config.neon');

    $configuration = new Configuration($neonBackend);
    $rootdir = dirname(dirname(__FILE__));
    $configuration['rootdir'] = $rootdir;

    return $configuration;
}

/**
 * @return \WildPHP\Core\Permissions\PermissionGroupCollection
 */
function setupPermissionGroupCollection()
{
    $globalPermissionGroup = new \WildPHP\Core\Permissions\PermissionGroupCollection();

    $dataStorage = DataStorageFactory::getStorage('permissiongroups');

    $groupsToLoad = $dataStorage->getAll();
    foreach ($groupsToLoad as $name => $groupState) {
        $pGroup = new PermissionGroup($groupState);
        $globalPermissionGroup->offsetSet($name, $pGroup);
    }

    return $globalPermissionGroup;
}

/**
 * @param ComponentContainer $container
 * @param ConnectionDetails $connectionDetails
 *
 * @return IrcConnection
 * @throws \Yoshi2889\Container\NotFoundException
 */
function setupIrcConnection(ComponentContainer $container, ConnectionDetails $connectionDetails)
{
    $loop = $container->getLoop();

    $ircConnection = new IrcConnection($container, $connectionDetails);
    $promise = $ircConnection->connect(
        ConnectorFactory::create(
            $container->getLoop(),
            $connectionDetails->getSecure(),
            $connectionDetails->getContextOptions()
        )
    );

    $promise->then(null, function (\Throwable $e) use ($container, $loop) {
        Logger::fromContainer($container)->error('An error occurred in the IRC connection:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        $loop->stop();
    });

    EventEmitter::fromContainer($container)
        ->on('stream.closed', [$loop, 'stop']);

    return $ircConnection;
}

/**
 * @param \React\EventLoop\LoopInterface $loop
 * @param Configuration $configuration
 * @param Logger $logger
 * @param ConnectionDetails $connectionDetails
 * @throws ReflectionException
 * @throws \WildPHP\Core\Modules\ModuleInitializationException
 * @throws \Yoshi2889\Container\ContainerException
 * @throws \Yoshi2889\Container\NotFoundException
 */
function createNewInstance(
    \React\EventLoop\LoopInterface $loop,
    Configuration $configuration,
    Logger $logger,
    ConnectionDetails $connectionDetails
) {
    $componentContainer = new ComponentContainer();
    $componentContainer->setLoop($loop);
    $componentContainer->add(new EventEmitter());
    $componentContainer->add($logger);
    $componentContainer->add($configuration);
    Logger::fromContainer($componentContainer)->info('WildPHP initializing');

    $componentContainer->add(new Queue());
    $componentContainer->add(new ChannelCollection());
    new CapabilityHandler($componentContainer);
    $componentContainer->add(setupPermissionGroupCollection());
    $componentContainer->add(setupIrcConnection($componentContainer, $connectionDetails));
    //$componentContainer->add(new Validator($componentContainer, $configuration['owner']));
    $componentContainer->add(new \WildPHP\Core\Commands\CommandRegistrar(new \WildPHP\Commands\CommandProcessor()));

    $componentContainer->add(new \WildPHP\Core\Database\Database(new \Medoo\Medoo([
        'database_type' => 'sqlite',
        'database_file' => WPHP_ROOT_DIR . '/state.sqlite'
    ])));

    $moduleFactory = new ModuleFactory($componentContainer);
    $componentContainer->add($moduleFactory);

    if (Configuration::fromContainer($componentContainer)->offsetExists('modules')) {
        $modules = Configuration::fromContainer($componentContainer)['modules'];
    }

    if (empty($modules) || !is_array($modules)) {
        $modules = [];
    }

    $modules = array_merge($modules, [
        \WildPHP\Core\Connection\Parser::class,
        \WildPHP\Core\Connection\PingPongHandler::class,
        \WildPHP\Core\Users\BotStateManager::class,
        \WildPHP\Core\Connection\NicknameHandler::class,
        \WildPHP\Core\Connection\MessageLogger::class,
        \WildPHP\Core\Connection\Capabilities\AccountNotifyHandler::class,
        \WildPHP\Core\Users\UserObserver::class,
        \WildPHP\Core\Channels\ChannelObserver::class,
        CommandRunner::class,
        \WildPHP\Core\Commands\HelpCommand::class,
        \WildPHP\Core\Permissions\PermissionGroupCommands::class,
        \WildPHP\Core\Permissions\PermissionCommands::class,
        \WildPHP\Core\Permissions\PermissionMembersCommands::class,
        \WildPHP\Core\Management\ManagementCommands::class
    ]);

    $moduleFactory->initializeModules($modules);

    EventEmitter::fromContainer($componentContainer)
        ->emit('wildphp.init-modules.after');

    Logger::fromContainer($componentContainer)->info('A connection has been set up successfully and will be started. This may take a while.',
        [
            'server' => $connectionDetails->getAddress() . ':' . $connectionDetails->getPort(),
            'wantedNickname' => $connectionDetails->getWantedNickname()
        ]);
}

$loop = LoopFactory::create();
$configuration = setupConfiguration();
$logger = setupLogger();

$connections = $configuration['connections'];

foreach ($connections as $connection) {
    $connectionDetails = new ConnectionDetails();
    $connectionDetails->setHostname(gethostname());
    $connectionDetails->setAddress($connection['server']);
    $connectionDetails->setPort($connection['port']);
    $connectionDetails->setUsername($connection['user']);
    $connectionDetails->setRealname($connection['realname']);
    $connectionDetails->setWantedNickname($connection['nick']);
    $connectionDetails->setPassword($connection['password'] ?? '');
    $connectionDetails->setSecure($connection['secure']);
    $connectionDetails->setContextOptions($connection['options'] ?? []);
    createNewInstance($loop, $configuration, $logger, $connectionDetails);
}

$loop->run();