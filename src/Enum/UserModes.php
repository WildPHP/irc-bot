<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Enum;


class UserModes extends Enum
{
    public static const IS_BOT = 'B';
    public static const ONLY_PREFIXED_MESSAGES = 'd';
    public static const NO_PRIVATE_MESSAGES = 'D';
    public static const CENSOR = 'G';
    public static const HIDE_IRCOP = 'H';
    public static const HIDE_ONLINE = 'I';
    public static const INVISIBLE = 'i';
    public static const IRCOP = 'o';
    public static const HIDE_JOINED_CHANNELS = 'p';
    public static const NO_KICK = 'q';
    public static const REGISTERED_NICKNAME = 'r';
    public static const ONLY_REGISTERED_PRIVATE_MESSAGES = 'R';
    public static const SERVICES_BOT = 'S';
    public static const SERVER_NOTICE_MASKS = 's';
    public static const NO_CTCP = 'T';
    public static const USING_VHOST = 't';
    public static const SHOW_WHOIS = 'W';
    public static const LISTEN_WALLOPS = 'w';
    public static const HIDDEN_NICKNAME = 'x';
    public static const ONLY_SECURE_PRIVATE_MESSAGES = 'Z';
    public static const USING_SECURE_CONNECTION = 'z';
}