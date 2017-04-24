<?php

use React\EventLoop\Factory as LoopFactory;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Channels\ChannelDataCollector;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\ConfigurationItem;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\Parser;
use WildPHP\Core\Tasks\TaskController;
use WildPHP\Core\Users\UserDataCollector;
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

Logger::initialize();
Configuration::initialize();
EventEmitter::initialize();

$loop = LoopFactory::create();
$connectorFactory = new \WildPHP\Core\Connection\ConnectorFactory($loop);

if (Configuration::get('secure')->getValue())
	$connector = $connectorFactory->createSecure();
else
	$connector = $connectorFactory->create();

$rootdir = dirname(dirname(__FILE__));
Configuration::set(new ConfigurationItem('rootdir', $rootdir));
\WildPHP\Core\Security\GlobalPermissionGroupCollection::setup();

$ircConnection = new IrcConnection();
$queue = new Queue();
$ircConnection->setQueue($queue);
$ircConnection->registerQueueFlusher($loop, $queue);
Parser::initialize($queue);
$pingPongHandler = new PingPongHandler();
$pingPongHandler->registerPingLoop($loop, $queue);

$username = Configuration::get('user')->getValue();
$hostname = gethostname();
$server = Configuration::get('server')->getValue();
$port = Configuration::get('port')->getValue();
$realname = Configuration::get('realname')->getValue();
$nickname = Configuration::get('nick')->getValue();

$ircConnection->createFromConnector($connector, $server, $port);
CapabilityHandler::initialize();
ChannelDataCollector::initialize();
UserDataCollector::initialize();
CommandHandler::initialize();
TaskController::setup($loop);

new \WildPHP\Core\Commands\HelpCommand();
new \WildPHP\Core\Security\PermissionCommands();
new \WildPHP\Core\Management\ManagementCommands();
new WildPHP\Core\Moderation\ModerationCommands();

include($rootdir . '/modules.php');

EventEmitter::on('stream.created', function (Queue $queue) use ($username, $hostname, $server, $realname, $nickname)
{
	$queue->user($username, $hostname, $server, $realname);
	$queue->nick($nickname);
});

EventEmitter::on('stream.closed', function () use ($loop)
{
	$loop->stop();
});

$loop->run();
