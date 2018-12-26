<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

return [
    \WildPHP\Core\Connection\MessageParser::class,
    \WildPHP\Core\Observers\ConnectionHeartbeatObserver::class,
    \WildPHP\Core\Observers\BotNicknameObserver::class,
    \WildPHP\Core\Observers\ServerConfigObserver::class,
    \WildPHP\Core\Connection\AlternativeNicknameHandler::class,
    \WildPHP\Core\Connection\MessageLogger::class,
    \WildPHP\Core\Connection\Capabilities\AccountNotifyHandler::class,
    \WildPHP\Core\Observers\UserObserver::class,
    \WildPHP\Core\Observers\ChannelObserver::class,
    \WildPHP\Core\Commands\CommandRunner::class,
    \WildPHP\Core\Queue\QueueProcessor::class,
    \WildPHP\Core\Connection\IncomingMessageParser::class,
    \WildPHP\Core\Connection\Capabilities\CapabilityHandler::class,
    //\WildPHP\Core\Commands\HelpCommand::class,
    //\WildPHP\Core\Permissions\PermissionGroupCommands::class,
    //\WildPHP\Core\Permissions\PermissionCommands::class,
    //\WildPHP\Core\Permissions\PermissionMembersCommands::class,
    //\WildPHP\Core\Management\ManagementCommands::class
];