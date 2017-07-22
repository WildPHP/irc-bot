<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use React\EventLoop\Factory as LoopFactory;
use ValidationClosures\Types;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Connection\ConnectorFactory;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\DataStorage\DataStorageFactory;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\ModuleFactory;
use WildPHP\Core\Permissions\PermissionGroup;
use WildPHP\Core\Permissions\Validator;
use Yoshi2889\Collections\Collection;

/**
 * @return Logger
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
	foreach ($groupsToLoad as $name => $groupState)
	{
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
 */
function setupIrcConnection(ComponentContainer $container, ConnectionDetails $connectionDetails)
{
	$loop = $container->getLoop();

	$ircConnection = new IrcConnection($container, $connectionDetails);
	$promise = $ircConnection->connect(ConnectorFactory::create($container->getLoop(), $connectionDetails->getSecure()));

	$promise->otherwise(function (\Throwable $e) use ($container, $loop)
	{
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
 */
function createNewInstance(\React\EventLoop\LoopInterface $loop, Configuration $configuration, Logger $logger, ConnectionDetails $connectionDetails)
{
	$componentContainer = new ComponentContainer();
	$componentContainer->setLoop($loop);
	$componentContainer->add(new EventEmitter());
	$componentContainer->add($logger);
	$componentContainer->add($configuration);
	Logger::fromContainer($componentContainer)->info('WildPHP initializing');

	$sasl = new \WildPHP\Core\Connection\SASL($componentContainer);
	$componentContainer->add(new CapabilityHandler($componentContainer, $sasl));
	$componentContainer->add(new CommandHandler($componentContainer, new Collection(Types::instanceof(\WildPHP\Core\Commands\Command::class))));

	$componentContainer->add(new Queue($componentContainer));
	$componentContainer->add(new ChannelCollection($componentContainer));
	$componentContainer->add(setupPermissionGroupCollection());
	$componentContainer->add(setupIrcConnection($componentContainer, $connectionDetails));
	$componentContainer->add(new Validator($componentContainer));

	$moduleFactory = new ModuleFactory($componentContainer);
	$componentContainer->add($moduleFactory);

	if (Configuration::fromContainer($componentContainer)->offsetExists('modules'))
		$modules = Configuration::fromContainer($componentContainer)['modules'];

	if (empty($modules) || !is_array($modules))
		$modules = [];

	$modules = array_merge($modules, [
		\WildPHP\Core\Connection\Parser::class,
		\WildPHP\Core\Connection\PingPongHandler::class,
		\WildPHP\Core\Users\UserStateManager::class,
		\WildPHP\Core\Channels\ChannelStateManager::class,
		\WildPHP\Core\Commands\HelpCommand::class,
		\WildPHP\Core\Permissions\PermissionCommands::class,
		\WildPHP\Core\Management\ManagementCommands::class,
		\WildPHP\Core\Users\BotStateManager::class,
		\WildPHP\Core\Connection\NicknameHandler::class,
		\WildPHP\Core\Connection\MessageLogger::class,
		\WildPHP\Core\Connection\AccountNotifyHandler::class
	]);

	$moduleFactory->initializeModules($modules);

	EventEmitter::fromContainer($componentContainer)
		->emit('wildphp.init-modules.after');

	Logger::fromContainer($componentContainer)->info('A connection has been set up successfully and will be started. This may take a while.', [
		'server' => $connectionDetails->getAddress() . ':' . $connectionDetails->getPort(),
		'wantedNickname' => $connectionDetails->getWantedNickname()
	]);
}

$loop = LoopFactory::create();
$configuration = setupConfiguration();
$logger = setupLogger();

$connections = $configuration['connections'];

foreach ($connections as $connection)
{
	$connectionDetails = new ConnectionDetails();
	$connectionDetails->setHostname(gethostname());
	$connectionDetails->setAddress($connection['server']);
	$connectionDetails->setPort($connection['port']);
	$connectionDetails->setUsername($connection['user']);
	$connectionDetails->setRealname($connection['realname']);
	$connectionDetails->setWantedNickname($connection['nick']);
	$connectionDetails->setPassword($connection['password'] ?? '');
	$connectionDetails->setSecure($connection['secure']);
	createNewInstance($loop, $configuration, $logger, $connectionDetails);
}

$loop->run();