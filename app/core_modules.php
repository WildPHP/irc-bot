<?php

/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

/** @noinspection PhpFullyQualifiedNameUsageInspection */
return [
    \WildPHP\Core\Storage\StorageCleaner::class,
    \WildPHP\Core\Connection\MessageParser::class,
    \WildPHP\Core\Commands\CommandRunner::class,
    \WildPHP\Queue\QueueProcessor::class,
    \WildPHP\Core\Connection\IncomingMessageParser::class,
    \WildPHP\Core\Connection\Capabilities\CapabilityHandler::class,

    // observers; please keep in alphabetical order
    \WildPHP\Core\Observers\AlternativeNicknameHandler::class,
    \WildPHP\Core\Observers\BotNicknameObserver::class,
    \WildPHP\Core\Observers\ConnectionHeartbeatObserver::class,
    \WildPHP\Core\Observers\EndOfNamesObserver::class,
    \WildPHP\Core\Observers\InitialBotUserCreator::class,
    \WildPHP\Core\Observers\InitialJoinObserver::class,
    \WildPHP\Core\Observers\JoinObserver::class,
    \WildPHP\Core\Observers\KickObserver::class,
    \WildPHP\Core\Observers\MessageLogger::class,
    \WildPHP\Core\Observers\ModeObserver::class,
    \WildPHP\Core\Observers\NamReplyObserver::class,
    \WildPHP\Core\Observers\NickObserver::class,
    \WildPHP\Core\Observers\PartObserver::class,
    \WildPHP\Core\Observers\QuitObserver::class,
    \WildPHP\Core\Observers\ServerConfigObserver::class,
    \WildPHP\Core\Observers\TopicObserver::class,
    \WildPHP\Core\Observers\WhosPcRplObserver::class,

    // commands; please keep in alphabetical order
    //\WildPHP\Core\Commands\HelpCommand::class,
    //\WildPHP\Core\Permissions\PermissionGroupCommands::class,
    //\WildPHP\Core\Permissions\PermissionCommands::class,
    //\WildPHP\Core\Permissions\PermissionMembersCommands::class,
    //\WildPHP\Core\Management\ManagementCommands::class
];
