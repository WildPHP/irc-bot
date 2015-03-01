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
 * Parses strings containing messages conforming to those in the IRC protocol
 * as described in RFC 1459.
 *
 * @category Phergie
 * @package Phergie\Irc
 * @link http://irchelp.org/irchelp/rfc/chapter2.html#c2_3
 * @link http://irchelp.org/irchelp/rfc/chapter4.html
 * @link http://irchelp.org/irchelp/rfc/chapter5.html
 */
interface ParserInterface
{
    /**
     * Parses data for a single IRC message from a given string into an array 
     * with a structure similar to the following:
     *
     * array(
     *     'prefix' => ':Angel',
     *     'nick' => 'Angel',
     *     'command' => 'USER',
     *     'params' => array(
     *         'username' => 'guest',
     *         'hostname' => 'tolmoon',
     *         'servername' => 'tolsun',
     *         'realname' => 'Ronnie Regan',
     *         'all' => 'guest tolmoon tolsun :Ronnie Regan',
     *     ),
     *     'targets' => array('guest'),
     *     'message' => "USER guest tolmoon tolsun :Ronnie Regan\r\n",
     *     'tail' => 'NICK :Wiz',
     * ),
     *
     * The prefix and its components and individual targets as described in
     * Section 2.3.1 of RFC 1459 are referenced by the 'prefix' and 'targets'
     * keys, respectively. A string containing the first full parsed message is
     * referenced by the 'message' key. Any remaining data after the first
     * message is referenced by the 'tail' key. The command and its parameters
     * are referenced by the 'command' and 'params' keys, respectively. Keys
     * used for parameters follow the naming conventions described in Section 4
     * of RFC 1459. See unit tests for Phergie\Irc\Parser for examples.
     *
     * @param string $message String containing the message to parse
     * @return array|null Associative array containing parsed data if a 
     *         message is successfully parsed, null otherwise
     */
    public function parse($message);

    /**
     * Parses all available data for one or more IRC messages from a given
     * string in the same way parse() does and returns an enumerated array of
     * associative arrays each conforming to structure of the return value of
     * parse().
     *
     * @param string $message String containing the message to parse
     * @return array Enumerated array of associative arrays each containing 
     *         parsed data for a single message if any messages are 
     *         successfully parsed, an empty array otherwise
     * @see \Phergie\Irc\ParserInterface::parse()
     */
    public function parseAll($message);

    /**
     * Parses data for a single IRC message from a given string in the same way
     * parse() does except that $message is passed by reference and the message
     * parsed from it is removed afterward.
     *
     * @param string $message String containing the message to parse
     * @return array|null Associative array containing parsed data if a 
     *         message is successfully parsed, null otherwise
     * @see \Phergie\Irc\ParserInterface::parse()
     */
    public function consume(&$message);

    /**
     * Parses all available data for one or more IRC messages from a given
     * string in the same way consume() does and returns an enumerated array of
     * associative arrays each conforming to structure of the return value of
     * parse().
     *
     * @param string $message String containing the message to parse
     * @return array Enumerated array of associative arrays each containing 
     *         parsed data for a single message if any messages are 
     *         successfully parsed, an empty array otherwise
     * @see \Phergie\Irc\ParserInterface::parse()
     * @see \Phergie\Irc\ParserInterface::consume()
     */
    public function consumeAll(&$message);
}
