<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

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
use WildPHP\Core\Observers\ConnectionHeartbeatObserver;
use WildPHP\Core\Observers\EndOfNamesObserver;
use WildPHP\Core\Observers\InitialJoinObserver;
use WildPHP\Core\Observers\JoinObserver;
use WildPHP\Core\Observers\KickObserver;
use WildPHP\Core\Observers\NamReplyObserver;
use WildPHP\Core\Observers\NickObserver;
use WildPHP\Core\Observers\PartObserver;
use WildPHP\Core\Observers\QuitObserver;
use WildPHP\Core\Observers\ServerConfigObserver;
use WildPHP\Core\Observers\TopicObserver;
use WildPHP\Core\Observers\WhosPcRplObserver;
use WildPHP\Core\Queue\QueueProcessor;

return [
    MessageParser::class,
    AlternativeNicknameHandler::class,
    MessageLogger::class,
    AccountNotifyHandler::class,
    CommandRunner::class,
    QueueProcessor::class,
    IncomingMessageParser::class,
    CapabilityHandler::class,

    // observers; please keep in alphabetical order
    BotNicknameObserver::class,
    ConnectionHeartbeatObserver::class,
    EndOfNamesObserver::class,
    InitialJoinObserver::class,
    JoinObserver::class,
    KickObserver::class,
    NamReplyObserver::class,
    NickObserver::class,
    PartObserver::class,
    QuitObserver::class,
    ServerConfigObserver::class,
    TopicObserver::class,
    WhosPcRplObserver::class,

    // commands; please keep in alphabetical order
    //\WildPHP\Core\Commands\HelpCommand::class,
    //\WildPHP\Core\Permissions\PermissionGroupCommands::class,
    //\WildPHP\Core\Permissions\PermissionCommands::class,
    //\WildPHP\Core\Permissions\PermissionMembersCommands::class,
    //\WildPHP\Core\Management\ManagementCommands::class
];
