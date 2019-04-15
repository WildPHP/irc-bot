<?php
declare(strict_types=1);
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use WildPHP\Core\Commands\CommandRunner;
use WildPHP\Core\Connection\AlternativeNicknameHandler;
use WildPHP\Core\Connection\Capabilities\AccountNotifyHandler;
use WildPHP\Core\Connection\Capabilities\CapabilityHandler;
use WildPHP\Core\Connection\IncomingMessageParser;
use WildPHP\Core\Connection\MessageLogger;
use WildPHP\Core\Connection\MessageParser;
use WildPHP\Core\Observers\BotNicknameObserver;
use WildPHP\Core\Observers\ChannelObserver;
use WildPHP\Core\Observers\ConnectionHeartbeatObserver;
use WildPHP\Core\Observers\ServerConfigObserver;
use WildPHP\Core\Observers\UserObserver;
use WildPHP\Core\Queue\QueueProcessor;

return [
    MessageParser::class,
    ConnectionHeartbeatObserver::class,
    BotNicknameObserver::class,
    ServerConfigObserver::class,
    AlternativeNicknameHandler::class,
    MessageLogger::class,
    AccountNotifyHandler::class,
    UserObserver::class,
    ChannelObserver::class,
    CommandRunner::class,
    QueueProcessor::class,
    IncomingMessageParser::class,
    CapabilityHandler::class,
    //\WildPHP\Core\Commands\HelpCommand::class,
    //\WildPHP\Core\Permissions\PermissionGroupCommands::class,
    //\WildPHP\Core\Permissions\PermissionCommands::class,
    //\WildPHP\Core\Permissions\PermissionMembersCommands::class,
    //\WildPHP\Core\Management\ManagementCommands::class
];
