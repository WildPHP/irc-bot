<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Enum;

class ChannelModes extends Enum
{
    public const PREFIX_OWNER = '~';
    public const PREFIX_ADMIN = '&';
    public const PREFIX_OP = '@';
    public const PREFIX_HALFOP = '%';
    public const PREFIX_VOICE = '+';

    public const ACCESS_OWNER = 'h';
    public const ACCESS_ADMIN = 'a';
    public const ACCESS_OP = 'o';
    public const ACCESS_VOICE = 'v';

    public const LIST_BAN = 'b';
    public const LIST_BAN_EXCEPT = 'e';
    public const LIST_INVITE_EXCEPT = 'l';

    public const SETTING_NO_COLOR = 'c';
    public const SETTING_NO_CTCP = 'C';
    public const SETTING_DELAY_JOIN = 'D';
    public const SETTING_DELAY_JOIN_UNSET_TEMP = 'd';
    public const SETTING_FLOOD_PROTECTION = 'f';
    public const SETTING_CENSOR = 'G';
    public const SETTING_INVITE_ONLY = 'i';
    public const SETTING_CHANNEL_KEY = 'k';
    public const SETTING_NO_KNOCK = 'K';
    public const SETTING_CHANNEL_LINK = 'L';
    public const SETTING_USER_LIMIT = 'l';
    public const SETTING_MODERATED = 'm';
    public const SETTING_REGISTERED_ONLY_SPEAK = 'M';
    public const SETTING_NO_NICK_CHANGE = 'N';
    public const SETTING_NO_EXTERNAL_MESSAGES = 'n';
    public const SETTING_IRCOP_ONLY = 'O';
    public const SETTING_PERMANENT = 'P';
    public const SETTING_PRIVATE = 'p';
    public const SETTING_NO_KICK = 'Q';
    public const SETTING_REGISTERED_ONLY = 'R';
    public const SETTING_CHANNEL_REGISTERED = 'r';
    public const SETTING_SECRET = 's';
    public const SETTING_STRIP_COLOR = 'S';
    public const SETTING_NO_NOTICE = 'T';
    public const SETTING_RESTRICT_TOPIC = 't';
    public const SETTING_NO_INVITE = 'V';
    public const SETTING_SECURE_ONLY = 'z';
    public const SETTING_IS_SECURE = 'Z';
}
