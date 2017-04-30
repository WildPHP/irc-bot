<?php

use React\EventLoop\Factory as LoopFactory;
use WildPHP\Core\Commands\Command;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\ConfigurationItem;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\DataStorage\DataStorage;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\Parser;
use WildPHP\Core\Security\PermissionGroup;
use WildPHP\Core\Tasks\TaskController;
use WildPHP\Core\Connection\PingPongHandler;

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function setupLogger()
{
	$klogger = new \Katzgrau\KLogger\Logger(WPHP_ROOT_DIR . '/logs');

	return new Logger($klogger);
}

function setupConfiguration()
{
	$neonBackend = new \WildPHP\Core\Configuration\NeonBackend(WPHP_ROOT_DIR . '/config.neon');

	$configuration = new Configuration($neonBackend);
	$rootdir = dirname(dirname(__FILE__));
	$configuration->set(new ConfigurationItem('rootdir', $rootdir));
	return $configuration;
}

function setupEventEmitter()
{
	return new Evenement\EventEmitter();
}

function setupPermissionGroupCollection()
{
	$globalPermissionGroup = new \WildPHP\Core\Security\PermissionGroupCollection();

	$dataStorage = new DataStorage('permissiongrouplist');

	$groupsToLoad = $dataStorage->get('groupstoload');
	foreach ($groupsToLoad as $group)
	{
		$pGroup = new PermissionGroup($group, true);
		$globalPermissionGroup->add($pGroup);
	}

	register_shutdown_function(function () use ($globalPermissionGroup)
	{
		$groups = $globalPermissionGroup->toArray();
		$groupList = [];

		foreach ($groups as $group)
		{
			$groupList[] = $group->getName();
		}

		$dataStorage = new DataStorage('permissiongrouplist');
		$dataStorage->set('groupstoload', $groupList);
	});

	return $globalPermissionGroup;
}

function setupIrcConnection(\WildPHP\Core\ComponentContainer $container)
{
	$loop = $container->getLoop();
	$configuration = $container->getConfiguration();
	$connectorFactory = new \WildPHP\Core\Connection\ConnectorFactory($loop);

	if ($container->getConfiguration()->get('secure')->getValue())
		$connector = $connectorFactory->createSecure();
	else
		$connector = $connectorFactory->create();

	$ircConnection = new IrcConnection($container);
	$queue = new Queue($container);
	$container->setQueue($queue);
	$ircConnection->registerQueueFlusher($loop, $queue);
	new Parser($container);
	$pingPongHandler = new PingPongHandler($container);
	$pingPongHandler->registerPingLoop($loop, $queue);

	$username = $configuration->get('user')->getValue();
	$hostname = gethostname();
	$server = $configuration->get('server')->getValue();
	$port = $configuration->get('port')->getValue();
	$realname = $configuration->get('realname')->getValue();
	$nickname = $configuration->get('nick')->getValue();

	$ircConnection->createFromConnector($connector, $server, $port);

	$container->getEventEmitter()->on('stream.created', function (Queue $queue) use ($username, $hostname, $server, $realname, $nickname)
	{
		$queue->user($username, $hostname, $server, $realname);
		$queue->nick($nickname);
	});

	$container->getEventEmitter()->on('stream.closed', function () use ($loop)
	{
		$loop->stop();
	});
	return $ircConnection;
}

$componentContainer = new \WildPHP\Core\ComponentContainer();
$componentContainer->setLoop(LoopFactory::create());
$componentContainer->setEventEmitter(setupEventEmitter());
$componentContainer->setLogger(setupLogger());
$componentContainer->setConfiguration(setupConfiguration());
$componentContainer->setCapabilityHandler(new CapabilityHandler($componentContainer));
$sasl = new \WildPHP\Core\Connection\SASL($componentContainer);
$componentContainer->getCapabilityHandler()->setSasl($sasl);
$componentContainer->setCommandHandler(new CommandHandler($componentContainer, new \Collections\Dictionary()));
$componentContainer->setTaskController(new TaskController($componentContainer));

$componentContainer->setChannelCollection(new \WildPHP\Core\Channels\ChannelCollection($componentContainer));
$componentContainer->setUserCollection(new \WildPHP\Core\Users\UserCollection($componentContainer));
$componentContainer->setPermissionGroupCollection(setupPermissionGroupCollection());
$componentContainer->setIrcConnection(setupIrcConnection($componentContainer));
$componentContainer->setValidator(new \WildPHP\Core\Security\Validator($componentContainer));


new \WildPHP\Core\Channels\ChannelStateManager($componentContainer);
new \WildPHP\Core\Users\UserStateManager($componentContainer);
new \WildPHP\Core\Commands\HelpCommand($componentContainer);
new \WildPHP\Core\Security\PermissionCommands($componentContainer);
new \WildPHP\Core\Management\ManagementCommands($componentContainer);
new WildPHP\Core\Moderation\ModerationCommands($componentContainer);

try
{
	$modules = $componentContainer->getConfiguration()->get('modules')->getValue();

	var_dump($modules);
	foreach ($modules as $module)
	{
		try
		{
			new $module($componentContainer);
		}
		catch (\Exception $e)
		{
			$componentContainer->getLogger()->error('Could not properly load module; stability not guaranteed!', [
				'class' => $module,
				'message' => $e->getMessage()
			]);
		}

	}
}
catch (\WildPHP\Core\Configuration\ConfigurationItemNotFoundException $e)
{
	echo $e->getMessage();
}

$componentContainer->getLoop()->run();
