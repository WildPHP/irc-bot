<?php

use React\EventLoop\Factory as LoopFactory;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Connection\ConnectionDetailsHolder;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;

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

ConnectionDetailsHolder::setServer(Configuration::get('server')->getValue());
ConnectionDetailsHolder::setPort(Configuration::get('port')->getValue());
ConnectionDetailsHolder::setInitialNickname(Configuration::get('nick')->getValue());
ConnectionDetailsHolder::setUsername(Configuration::get('user')->getValue());
ConnectionDetailsHolder::setRealname(Configuration::get('realname')->getValue());
ConnectionDetailsHolder::setIsSecure(Configuration::get('secure')->getValue());

if (ConnectionDetailsHolder::getIsSecure())
    $connector = $connectorFactory->createSecure();
else
    $connector = $connectorFactory->create();

$ircConnection = new IrcConnection();
$queue = new Queue();
$ircConnection->registerQueueFlusher($loop, $queue);
$ircConnection->createFromConnector($connector);

$queue->user(ConnectionDetailsHolder::getUsername(), gethostname(), ConnectionDetailsHolder::getServer(), ConnectionDetailsHolder::getRealname());
$queue->nick(ConnectionDetailsHolder::getInitialNickname());

$loop->run();