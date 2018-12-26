<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Enum;

class ChannelModes extends Enum
{
    public static const PREFIX_OWNER = '~';
    public static const PREFIX_ADMIN = '&';
    public static const PREFIX_OP = '@';
    public static const PREFIX_HALFOP = '%';
    public static const PREFIX_VOICE = '+';

    public static const ACCESS_OWNER = 'h';
    public static const ACCESS_ADMIN = 'a';
    public static const ACCESS_OP = 'o';
    public static const ACCESS_VOICE = 'v';

    public static const LIST_BAN = 'b';
    public static const LIST_BAN_EXCEPT = 'e';
    public static const LIST_INVITE_EXCEPT = 'l';

    public static const SETTING_NO_COLOR = 'c';
    public static const SETTING_NO_CTCP = 'C';
    public static const SETTING_DELAY_JOIN = 'D';
    public static const SETTING_DELAY_JOIN_UNSET_TEMP = 'd';
    public static const SETTING_FLOOD_PROTECTION = 'f';
    public static const SETTING_CENSOR = 'G';
    public static const SETTING_INVITE_ONLY = 'i';
    public static const SETTING_CHANNEL_KEY = 'k';
    public static const SETTING_NO_KNOCK = 'K';
    public static const SETTING_CHANNEL_LINK = 'L';
    public static const SETTING_USER_LIMIT = 'l';
    public static const SETTING_MODERATED = 'm';
    public static const SETTING_REGISTERED_ONLY_SPEAK = 'M';
    public static const SETTING_NO_NICK_CHANGE = 'N';
    public static const SETTING_NO_EXTERNAL_MESSAGES = 'n';
    public static const SETTING_IRCOP_ONLY = 'O';
    public static const SETTING_PERMANENT = 'P';
    public static const SETTING_PRIVATE = 'p';
    public static const SETTING_NO_KICK = 'Q';
    public static const SETTING_REGISTERED_ONLY = 'R';
    public static const SETTING_CHANNEL_REGISTERED = 'r';
    public static const SETTING_SECRET = 's';
    public static const SETTING_STRIP_COLOR = 'S';
    public static const SETTING_NO_NOTICE = 'T';
    public static const SETTING_RESTRICT_TOPIC = 't';
    public static const SETTING_NO_INVITE = 'V';
    public static const SETTING_SECURE_ONLY = 'z';
    public static const SETTING_IS_SECURE = 'Z';

}