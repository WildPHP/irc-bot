<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-parser for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc
 */

namespace Phergie\Irc;

/**
 * Canonical implementation of ParserInterface.
 *
 * @category Phergie
 * @package Phergie\Irc
 */
class Parser implements ParserInterface
{
    /**
     * Regular expression to match a single IRC message
     *
     * @var string
     */
    protected $message;

    /**
     * Regular expression to parse target information from the first parameter
     * of a single IRC message
     *
     * @var string
     */
    protected $target;

    /**
     * Regular expression to parse channel names
     *
     * @var string
     */
    protected $channel;

    /**
     * Regular expression to parse CTCP commands
     *
     * @var string
     * @link http://irchelp.org/irchelp/rfc/ctcpspec.html
     */
    protected $ctcp;

    /**
     * Mapping of CTCP commands to corresponding regular expressions for
     * parsing parameter values for those commands
     *
     * @var array
     */
    protected $ctcpParams;

    /**
     * Mapping of IRC commands to corresponding regular expressions for
     * parsing parameter values for those commands
     *
     * @var array
     */
    protected $params;

    /**
     * Mapping of numeric replies to their descriptive codes
     *
     * @var array
     */
    protected $replies = array(
        '401' => 'ERR_NOSUCHNICK',
        '402' => 'ERR_NOSUCHSERVER',
        '403' => 'ERR_NOSUCHCHANNEL',
        '404' => 'ERR_CANNOTSENDTOCHAN',
        '405' => 'ERR_TOOMANYCHANNELS',
        '406' => 'ERR_WASNOSUCHNICK',
        '407' => 'ERR_TOOMANYTARGETS',
        '409' => 'ERR_NOORIGIN',
        '411' => 'ERR_NORECIPIENT',
        '412' => 'ERR_NOTEXTTOSEND',
        '413' => 'ERR_NOTOPLEVEL',
        '414' => 'ERR_WILDTOPLEVEL',
        '421' => 'ERR_UNKNOWNCOMMAND',
        '422' => 'ERR_NOMOTD',
        '423' => 'ERR_NOADMININFO',
        '424' => 'ERR_FILEERROR',
        '431' => 'ERR_NONICKNAMEGIVEN',
        '432' => 'ERR_ERRONEUSNICKNAME',
        '433' => 'ERR_NICKNAMEINUSE',
        '436' => 'ERR_NICKCOLLISION',
        '441' => 'ERR_USERNOTINCHANNEL',
        '442' => 'ERR_NOTONCHANNEL',
        '443' => 'ERR_USERONCHANNEL',
        '444' => 'ERR_NOLOGIN',
        '445' => 'ERR_SUMMONDISABLED',
        '446' => 'ERR_USERSDISABLED',
        '451' => 'ERR_NOTREGISTERED',
        '461' => 'ERR_NEEDMOREPARAMS',
        '462' => 'ERR_ALREADYREGISTRED',
        '463' => 'ERR_NOPERMFORHOST',
        '464' => 'ERR_PASSWDMISMATCH',
        '465' => 'ERR_YOUREBANNEDCREEP',
        '467' => 'ERR_KEYSET',
        '471' => 'ERR_CHANNELISFULL',
        '472' => 'ERR_UNKNOWNMODE',
        '473' => 'ERR_INVITEONLYCHAN',
        '474' => 'ERR_BANNEDFROMCHAN',
        '475' => 'ERR_BADCHANNELKEY',
        '481' => 'ERR_NOPRIVILEGES',
        '482' => 'ERR_CHANOPRIVSNEEDED',
        '483' => 'ERR_CANTKILLSERVER',
        '491' => 'ERR_NOOPERHOST',
        '501' => 'ERR_UMODEUNKNOWNFLAG',
        '502' => 'ERR_USERSDONTMATCH',
        '300' => 'RPL_NONE',
        '302' => 'RPL_USERHOST',
        '303' => 'RPL_ISON',
        '301' => 'RPL_AWAY',
        '305' => 'RPL_UNAWAY',
        '306' => 'RPL_NOWAWAY',
        '311' => 'RPL_WHOISUSER',
        '312' => 'RPL_WHOISSERVER',
        '313' => 'RPL_WHOISOPERATOR',
        '317' => 'RPL_WHOISIDLE',
        '318' => 'RPL_ENDOFWHOIS',
        '319' => 'RPL_WHOISCHANNELS',
        '314' => 'RPL_WHOWASUSER',
        '369' => 'RPL_ENDOFWHOWAS',
        '321' => 'RPL_LISTSTART',
        '322' => 'RPL_LIST',
        '323' => 'RPL_LISTEND',
        '324' => 'RPL_CHANNELMODEIS',
        '331' => 'RPL_NOTOPIC',
        '332' => 'RPL_TOPIC',
        '341' => 'RPL_INVITING',
        '342' => 'RPL_SUMMONING',
        '351' => 'RPL_VERSION',
        '352' => 'RPL_WHOREPLY',
        '315' => 'RPL_ENDOFWHO',
        '353' => 'RPL_NAMREPLY',
        '366' => 'RPL_ENDOFNAMES',
        '364' => 'RPL_LINKS',
        '365' => 'RPL_ENDOFLINKS',
        '367' => 'RPL_BANLIST',
        '368' => 'RPL_ENDOFBANLIST',
        '371' => 'RPL_INFO',
        '374' => 'RPL_ENDOFINFO',
        '375' => 'RPL_MOTDSTART',
        '372' => 'RPL_MOTD',
        '376' => 'RPL_ENDOFMOTD',
        '381' => 'RPL_YOUREOPER',
        '382' => 'RPL_REHASHING',
        '391' => 'RPL_TIME',
        '392' => 'RPL_USERSSTART',
        '393' => 'RPL_USERS',
        '394' => 'RPL_ENDOFUSERS',
        '395' => 'RPL_NOUSERS',
        '200' => 'RPL_TRACELINK',
        '201' => 'RPL_TRACECONNECTING',
        '202' => 'RPL_TRACEHANDSHAKE',
        '203' => 'RPL_TRACEUNKNOWN',
        '204' => 'RPL_TRACEOPERATOR',
        '205' => 'RPL_TRACEUSER',
        '206' => 'RPL_TRACESERVER',
        '208' => 'RPL_TRACENEWTYPE',
        '261' => 'RPL_TRACELOG',
        '211' => 'RPL_STATSLINKINFO',
        '212' => 'RPL_STATSCOMMANDS',
        '213' => 'RPL_STATSCLINE',
        '214' => 'RPL_STATSNLINE',
        '215' => 'RPL_STATSILINE',
        '216' => 'RPL_STATSKLINE',
        '218' => 'RPL_STATSYLINE',
        '219' => 'RPL_ENDOFSTATS',
        '241' => 'RPL_STATSLLINE',
        '242' => 'RPL_STATSUPTIME',
        '243' => 'RPL_STATSOLINE',
        '244' => 'RPL_STATSHLINE',
        '221' => 'RPL_UMODEIS',
        '251' => 'RPL_LUSERCLIENT',
        '252' => 'RPL_LUSEROP',
        '253' => 'RPL_LUSERUNKNOWN',
        '254' => 'RPL_LUSERCHANNELS',
        '255' => 'RPL_LUSERME',
        '256' => 'RPL_ADMINME',
        '257' => 'RPL_ADMINLOC1',
        '258' => 'RPL_ADMINLOC2',
        '259' => 'RPL_ADMINEMAIL',
    );

    /**
     * Constructs regular expressions used to parse messages.
     */
    public function __construct()
    {
        $crlf = "\r\n";
        $letter = 'a-zA-Z';
        $number = '0-9';
        $special = preg_quote('[]\`_^{|}');
        $null = '\\x00';
        $command = "(?P<command>[$letter]+|[$number]{3})";
        $middle = "(?: [^ $null$crlf:][^ $null$crlf]*)";
        // ? provides for relaxed parsing of messages without trailing parameters properly demarcated
        $trailing = "(?: :?[^$null$crlf]*)";
        $params = "(?P<params>$trailing?|(?:$middle{0,14}$trailing))";
        $name = "[$letter](?:[$letter$number\\-]*[$letter$number])?";
        $host = "$name(?:\\.(?:$name)*)+";
        $nick = "(?:[$letter$special][$letter$number$special-]*)";
        $user = "(?:[^ $null$crlf]+)";
        $prefix = "(?:(?P<servername>$host)|(?:(?P<nick>$nick)(?:!(?P<user>$user))?(?:@(?P<host>$host))?))";
        $message = "(?P<prefix>:$prefix )?$command$params$crlf";
        $this->message = "/^$message/SU";

        $chstring = "[^ \a$null$crlf,]+";
        $channel = $this->channel = "(?:[#&]$chstring)";
        $mask = "(?:[#$]$chstring)";
        $to = "(?:$channel|(?:$user@$host)|$nick|$mask)";
        $target = "(?:$to(?:,$to)*)";
        $this->target = "/^$target$/S";

        $this->params = array(
            'PASS'     => "/^(?:(?P<password>$trailing))$/",
            'NICK'     => "/^(?:(?P<nickname>$middle|$trailing)(?P<hopcount>$trailing)?)$/",
            'USER'     => "/^(?:(?P<username>$middle)(?P<hostname>$middle)(?P<servername>$middle)(?P<realname>$trailing))$/",
            'SERVER'   => "/^(?:(?P<servername>$middle)(?P<hopcount>$middle)(?P<info>$trailing))$/",
            'OPER'     => "/^(?:(?P<user>$middle)(?P<password>$trailing))$/",
            'QUIT'     => "/^(?:(?P<message>$trailing)?)$/",
            'SQUIT'    => "/^(?:(?P<server>$middle)(?P<comment>$trailing))$/",
            'JOIN'     => "/^(?:(?P<channels>$middle|$trailing)(?P<keys>$trailing)?)$/",
            'PART'     => "/^(?:(?P<channels>$middle|$trailing)(?P<message>$trailing)?)$/",
            'MODE'     => "/^(?:(?P<target>$middle)(?P<mode>$middle|$trailing)(?P<param>$trailing)?)$/",
            'TOPIC'    => "/^(?:(?P<channel>$middle|$trailing)(?P<topic>$trailing)?)$/",
            'NAMES'    => "/^(?:(?P<channels>$trailing))$/",
            'LIST'     => "/^(?:(?:(?P<channels>$trailing)|$middle)?(?P<server>$trailing)?)$/",
            'INVITE'   => "/^(?:(?P<nickname>$middle)(?P<channel>$trailing))$/",
            'KICK'     => "/^(?:(?P<channel>$middle)(?P<user>$middle|$trailing)(?P<comment>$trailing)?)$/",
            'VERSION'  => "/^(?:(?P<server>$trailing)?)$/",
            'STATS'    => "/^(?:(?P<query>$middle|$trailing)(?P<server>$trailing)?)$/",
            'LINKS'    => "/^(?:(?P<remoteserver>$middle)?(?P<servermask>$trailing)?)$/",
            'TIME'     => "/^(?:(?P<server>$trailing)?)$/",
            'CONNECT'  => "/^(?:(?P<targetserver>$middle|$trailing)(?P<port>$middle|$trailing)?(?P<remoteserver>$trailing)?)$/",
            'TRACE'    => "/^(?:(?P<server>$trailing)?)$/",
            'ADMIN'    => "/^(?:(?P<server>$trailing)?)$/",
            'INFO'     => "/^(?:(?P<server>$trailing)?)$/",
            'PRIVMSG'  => "/^(?:(?P<receivers>$middle)(?P<text>$trailing))$/S",
            'NOTICE'   => "/^(?:(?P<nickname>$middle)(?P<text>$trailing))$/S",
            'WHO'      => "/^(?:(?P<name>$middle|$trailing)(?P<o>$trailing)?)$/",
            'WHOIS'    => "/^(?:(?P<server>$middle)?(?P<nickmasks>$trailing))$/",
            'WHOWAS'   => "/^(?:(?P<nickname>$middle|$trailing)(?P<count>$middle|$trailing)?(?P<server>$trailing)?)$/",
            'KILL'     => "/^(?:(?P<nickname>$middle)(?P<comment>$trailing))$/",
            'PING'     => "/^(?:(?P<server1>$middle|$trailing)(?P<server2>$trailing)?)$/",
            'PONG'     => "/^(?:(?P<daemon>$middle|$trailing)(?P<daemon2>$trailing)?)$/",
            'ERROR'    => "/^(?:(?P<message>$trailing))$/",
            'AWAY'     => "/^(?:(?P<message>$trailing)?)$/",
            'REHASH'   => "/^$/",
            'RESTART'  => "/^$/",
            'SUMMON'   => "/^(?:(?P<user>$middle|$trailing)(?P<server>$trailing)?)$/",
            'USERS'    => "/^(?:(?P<server>$trailing)?)$/",
            'WALLOPS'  => "/^(?:(?P<text>$trailing))$/",
            'USERHOST' => "/^(?:(?P<nickname1>$middle|$trailing)(?P<nickname2>$middle|$trailing)?(?P<nickname3>$middle|$trailing)?(?P<nickname4>$middle|$trailing)?(?P<nickname5>$trailing)?)$/",
            'ISON'     => "/^(?:(?P<nicknames>(?:$middle )*$trailing))$/",
        );

        $xdelim = "\001";
        $middle = "(?:[^:]+)";
        $trailing = "(?:[^$xdelim]+)";
        $this->ctcp = "/^$xdelim(?P<command>[^ ]+)(?P<params> [^$xdelim]+)?$xdelim$/S";
        $this->ctcpParams = array(
            'FINGER'     => "/^(?::(?P<user>$trailing))?$/",
            'VERSION'    => "/^(?:(?P<name>$middle):(?P<version>$middle):(?P<environment>$trailing))?$/",
            'SOURCE'     => "/^(?:(?P<host>$middle):(?P<directories>$middle):(?P<files>$trailing))?$/",
            'USERINFO'   => "/^(?::(?P<user>$trailing))?$/",
            'CLIENTINFO' => "/^(?::(?P<client>$trailing))?$/",
            'ERRMSG'     => "/^(?:(?P<query>.+)(?: :(?P<message>$trailing))?)$/U",
            'PING'       => "/^(?:(?P<timestamp>$trailing))$/",
            'TIME'       => "/^(?::(?P<time>$trailing))$/",
            'ACTION'       => "/^(?::(?P<action>$trailing))$/",
        );
    }

    /**
     * Strips leading space and colon from a given parameter string.
     *
     * @param string $param
     * @return string
     */
    protected function strip($param)
    {
        return preg_replace('/^ :?/', '', $param);
    }

    /**
     * Removes elements with numeric keys from an array.
     *
     * @param array $array
     * @return array
     */
    protected function removeIntegerKeys(array $array)
    {
        foreach (array_keys($array) as $key) {
            if (is_int($key)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Implements ParserInterface::parse().
     *
     * @param string $message Data stream containing the message to parse
     * @return array|null Associative array containing parsed data if the
     *         message is successfully parsed, null otherwise
     * @see \Phergie\Irc\ParserInterface::parse()
     */
    public function parse($message)
    {
        // Extract the first full message or bail if there is none
        if (!preg_match($this->message, $message, $parsed)) {
            return null;
        }

        // Parse out the first full message and prefix if present
        $parsed['message'] = $parsed[0];
        if (isset($parsed['prefix'])) {
            $parsed['prefix'] = rtrim($parsed['prefix']);
        }

        // Parse command parameters
        $command = strtoupper($parsed['command']);
        if (isset($this->params[$command]) && !empty($parsed['params'])) {
            preg_match($this->params[$command], $parsed['params'], $params);
            $params = array_map(array($this, 'strip'), $params);

            // Parse targets if present
            if (isset($params[1]) && preg_match($this->target, $params[1], $targets)) {
                $parsed['targets'] = explode(',', $this->strip($targets[0]));
            }

            switch ($command) {
                // Handle MODE-specific parameters
                case 'MODE':
                    if (preg_match('/^' . $this->channel . '$/', $params['target'])) {
                        $params['channel'] = $params['target'];
                        if (strpos($params['mode'], 'l') !== false) {
                            $params['limit'] = $params['param'];
                        } elseif (strpos($params['mode'], 'b') !== false
                            && !empty($params['param'])) {
                            $params['banmask'] = $params['param'];
                        } elseif (strpos($params['mode'], 'k') !== false) {
                            $params['key'] = $params['param'];
                        } elseif (isset($params['param'])) {
                            $params['user'] = $params['param'];
                        }
                    } else {
                        $params['user'] = $params['target'];
                    }
                    unset($params['target'], $params['param']);
                    break;
                // Handle CTCP messages
                case 'PRIVMSG':
                case 'NOTICE':
                    if ($params && preg_match($this->ctcp, end($params), $ctcp)) {
                        $parsed['ctcp'] = $this->removeIntegerKeys($ctcp);
                        if (isset($this->ctcpParams[$parsed['ctcp']['command']])
                            && !empty($parsed['ctcp']['params'])) {
                            $ctcpParams = ltrim($parsed['ctcp']['params']);
                            preg_match($this->ctcpParams[$parsed['ctcp']['command']], $ctcpParams, $ctcpParsed);
                            $parsed['ctcp']['params'] = array_merge(
                                $this->removeIntegerKeys($ctcpParsed),
                                array('all' => $ctcpParams)
                            );
                        }
                    }
                    break;
            }

            // Clean up and store the processed parameters
            $params = array_merge(array('all' => $params[0]), array_filter($params));
            $params = $this->removeIntegerKeys($params);
            $parsed['params'] = $params;
        } elseif (ctype_digit($command)) {
            if (isset($this->replies[$command])) {
                $parsed['code'] = $this->replies[$command];
            } else {
                $parsed['code'] = $command;
            }
            if (!empty($parsed['params'])) {
                $all = $this->strip($parsed['params']);
                if (strpos($parsed['params'], ' :') !== false) {
                    list($head, $tail) = explode(' :', $parsed['params'], 2);
                } else {
                    $head = $parsed['params'];
                    $tail = '';
                }
                $params = explode(' ', $head);
                $params[] = $tail;
                $parsed['params'] = array_filter($params);
                $parsed['params']['all'] = $all;
            }
        }

        // Store the remainder of the original data stream
        $length = strlen($parsed[0]);
        if ($length < strlen($message)) {
            $parsed['tail'] = substr($message, $length);
        }

        // Clean up and return the response
        $parsed = $this->removeIntegerKeys($parsed);
        $parsed = array_filter($parsed);
        return $parsed;
    }

    /**
     * Implements ParserInterface::parseAll().
     *
     * @param string $message String containing the message to parse
     * @return array Enumerated array of associative arrays each containing
     *         parsed data for a single message if any messages are
     *         successfully parsed, an empty array otherwise
     * @see \Phergie\Irc\ParserInterface::parseAll()
     */
    public function parseAll($message)
    {
        $tail = $message;
        $messages = array();
        do {
            $parsed = $this->parse($tail);
            if ($parsed === null) {
                break;
            }
            $messages[] = $parsed;
            $tail = empty($parsed['tail']) ? null : $parsed['tail'];
        } while ($tail !== null);
        return $messages;
    }

    /**
     * Implements ParserInterface::consume().
     *
     * @param string $message String containing the message to parse
     * @return array|null Associative array containing parsed data if a
     *         message is successfully parsed, null otherwise
     * @see \Phergie\Irc\ParserInterface::consume()
     */
    public function consume(&$message)
    {
        $parsed = $this->parse($message);
        $message = empty($parsed['tail']) ? '' : $parsed['tail'];
        return $parsed;
    }

    /**
     * Implements ParserInterface::consumeAll().
     *
     * @param string $message String containing the message to parse
     * @return array Enumerated array of associative arrays each containing
     *         parsed data for a single message if any messages are
     *         successfully parsed, an empty array otherwise
     * @see \Phergie\Irc\ParserInterface::consumeAll()
     */
    public function consumeAll(&$message)
    {
        $parsed = $this->parseAll($message);
        if ($parsed) {
            $last = end($parsed);
            $message = empty($last['tail']) ? '' : $last['tail'];
        }
        return $parsed;
    }
}
