<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Connection;


class ConnectionDetailsHolder
{
    /**
     * @var string
     */
    protected static $server = '';

    /**
     * @var int
     */
    protected static $port = 0;

    /**
     * @var string
     */
    protected static $initialNickname = '';

    /**
     * @var string
     */
    protected static $username = '';

    /**
     * @var string
     */
    protected static $realname = '';

    /**
     * @return string
     */
    public static function getServer(): string
    {
        return self::$server;
    }

    /**
     * @param string $server
     */
    public static function setServer(string $server)
    {
        self::$server = $server;
    }

    /**
     * @return int
     */
    public static function getPort(): int
    {
        return self::$port;
    }

    /**
     * @param int $port
     */
    public static function setPort(int $port)
    {
        self::$port = $port;
    }

    /**
     * @return string
     */
    public static function getInitialNickname(): string
    {
        return self::$initialNickname;
    }

    /**
     * @param string $initialNickname
     */
    public static function setInitialNickname(string $initialNickname)
    {
        self::$initialNickname = $initialNickname;
    }

    /**
     * @return string
     */
    public static function getUsername(): string
    {
        return self::$username;
    }

    /**
     * @param string $username
     */
    public static function setUsername(string $username)
    {
        self::$username = $username;
    }

    /**
     * @return string
     */
    public static function getRealname(): string
    {
        return self::$realname;
    }

    /**
     * @param string $realname
     */
    public static function setRealname(string $realname)
    {
        self::$realname = $realname;
    }
}