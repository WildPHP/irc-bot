<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;


class AllowedBy
{
    const BOT_OWNER = 'owner';
    const GROUP = 'group';
    const IRC_ACCOUNT = 'ircAccount';
    const CHANNEL_MODE = 'channelMode';
    const NONE = false;

    const DENIED_MESSAGE = 'You do not have sufficient permission to use this command. Required policy is: %s';
}