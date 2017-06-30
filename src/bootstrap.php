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
use WildPHP\Core\Configuration\ConfigurationItem;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Connection\ConnectorFactory;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\DataStorage\DataStorageFactory;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Security\PermissionGroup;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Tasks\TaskController;
use WildPHP\Core\Users\UserCollection;
use Yoshi2889\Collections\Collection;

/**
 * @param Configuration $configuration
 *
 * @return Logger
 */
function setupLogger(Configuration $configuration): Logger
{
	try
	{
		$logLevel = $configuration->get('loglevel')
			->getValue();

		if (!in_array($logLevel, ['debug', 'info', 'warning', 'error']))
			$logLevel = 'info';
	}
	catch (\Exception $e)
	{
		$logLevel = 'info';
	}
	$klogger = new \Katzgrau\KLogger\Logger(WPHP_ROOT_DIR . '/logs', $logLevel);

	return new Logger($klogger);
}

/**
 * @return Configuration
 */
function setupConfiguration()
{
	$neonBackend = new \WildPHP\Core\Configuration\NeonBackend(WPHP_ROOT_DIR . '/config.neon');

	$configuration = new Configuration($neonBackend);
	$rootdir = dirname(dirname(__FILE__));
	$configuration->set(new ConfigurationItem('rootdir', $rootdir));

	return $configuration;
}

/**
 * @return EventEmitter
 */
function setupEventEmitter()
{
	return new EventEmitter();
}

/**
 * @return \WildPHP\Core\Security\PermissionGroupCollection
 */
function setupPermissionGroupCollection()
{
	$globalPermissionGroup = new \WildPHP\Core\Security\PermissionGroupCollection();

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

	$promise->otherwise(function (\Exception $e) use ($container, $loop)
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
	$componentContainer->add(setupEventEmitter());
	$componentContainer->add($logger);
	$componentContainer->add($configuration);
	Logger::fromContainer($componentContainer)->info('WildPHP initializing');

	$capabilityHandler = new CapabilityHandler($componentContainer);
	$componentContainer->add($capabilityHandler);
	$sasl = new \WildPHP\Core\Connection\SASL($componentContainer);
	$capabilityHandler->setSasl($sasl);
	$componentContainer->add(new CommandHandler($componentContainer, new Collection(Types:: instanceof (\WildPHP\Core\Commands\Command::class))));
	$componentContainer->add(new TaskController($componentContainer));

	$componentContainer->add(new Queue($componentContainer));
	$componentContainer->add(new ChannelCollection($componentContainer));
	$componentContainer->add(new UserCollection($componentContainer));
	$componentContainer->add(setupPermissionGroupCollection());
	$componentContainer->add(setupIrcConnection($componentContainer, $connectionDetails));
	$componentContainer->add(new Validator($componentContainer));

	try
	{
		$modules = Configuration::fromContainer($componentContainer)
			->get('modules')
			->getValue();
	}
	catch (\WildPHP\Core\Configuration\ConfigurationItemNotFoundException $e)
	{
	}

	if (empty($modules) || !is_array($modules))
		$modules = [];

	$modules = array_merge($modules, [
		\WildPHP\Core\Connection\Parser::class,
		\WildPHP\Core\Connection\PingPongHandler::class,
		\WildPHP\Core\Channels\ChannelStateManager::class,
		\WildPHP\Core\Users\UserStateManager::class,
		\WildPHP\Core\Commands\HelpCommand::class,
		\WildPHP\Core\Security\PermissionCommands::class,
		\WildPHP\Core\Management\ManagementCommands::class,
		\WildPHP\Core\Moderation\ModerationCommands::class,
		\WildPHP\Core\Users\BotStateManager::class
	]);

	foreach ($modules as $module)
	{
		try
		{
			new $module($componentContainer);
			Logger::fromContainer($componentContainer)->info('Loaded module with class ' . $module);
		}
		catch (\Exception $e)
		{
			Logger::fromContainer($componentContainer)->error('Could not properly load module; stability not guaranteed!',
				[
					'class' => $module,
					'exception' => get_class($e),
					'message' => $e->getMessage(),
				]);
		}

	}

	EventEmitter::fromContainer($componentContainer)
		->emit('wildphp.init-modules.after');

	Logger::fromContainer($componentContainer)->info('A connection has been set up successfully and will be started. This may take a while.', [
		'server' => $connectionDetails->getAddress() . ':' . $connectionDetails->getPort(),
		'wantedNickname' => $connectionDetails->getWantedNickname()
	]);
}

$loop = LoopFactory::create();
$configuration = setupConfiguration();
$logger = setupLogger($configuration);

$connections = $configuration->get('connections')
	->getValue();

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