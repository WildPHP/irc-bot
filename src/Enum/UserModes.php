<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Enum;

class UserModes extends Enum
{
    public const IS_BOT = 'B';
    public const ONLY_PREFIXED_MESSAGES = 'd';
    public const NO_PRIVATE_MESSAGES = 'D';
    public const CENSOR = 'G';
    public const HIDE_IRCOP = 'H';
    public const HIDE_ONLINE = 'I';
    public const INVISIBLE = 'i';
    public const IRCOP = 'o';
    public const HIDE_JOINED_CHANNELS = 'p';
    public const NO_KICK = 'q';
    public const REGISTERED_NICKNAME = 'r';
    public const ONLY_REGISTERED_PRIVATE_MESSAGES = 'R';
    public const SERVICES_BOT = 'S';
    public const SERVER_NOTICE_MASKS = 's';
    public const NO_CTCP = 'T';
    public const USING_VHOST = 't';
    public const SHOW_WHOIS = 'W';
    public const LISTEN_WALLOPS = 'w';
    public const HIDDEN_NICKNAME = 'x';
    public const ONLY_SECURE_PRIVATE_MESSAGES = 'Z';
    public const USING_SECURE_CONNECTION = 'z';
}
