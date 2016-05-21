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

namespace WildPHP\Core\Logger;

class Logger
{
    /**
     * @var \Katzgrau\KLogger\Logger
     */
    protected static $logger = null;
    
    public static function initialize()
    {
        self::$logger = new \Katzgrau\KLogger\Logger(WPHP_ROOT_DIR . '/logs');
        self::info('WildPHP starting up!');
    }

    // KLogger does not natively support writing to stdout. This function works around that.
    public static function echoLastLine()
    {
        $lastline = self::$logger->getLastLogLine();
        echo $lastline . PHP_EOL;
    }

    public static function emergency(string $message, array $context = [])
    {
        self::$logger->emergency($message, $context);
        self::echoLastLine();
    }

    public static function alert(string $message, array $context = [])
    {
        self::$logger->alert($message, $context);
        self::echoLastLine();
    }

    public static function critical(string $message, array $context = [])
    {
        self::$logger->critical($message, $context);
        self::echoLastLine();
    }

    public static function error(string $message, array $context = [])
    {
        self::$logger->error($message, $context);
        self::echoLastLine();
    }

    public static function warning(string $message, array $context = [])
    {
        self::$logger->warning($message, $context);
        self::echoLastLine();
    }

    public static function notice(string $message, array $context = [])
    {
        self::$logger->notice($message, $context);
        self::echoLastLine();
    }

    public static function info(string $message, array $context = [])
    {
        self::$logger->info($message, $context);
        self::echoLastLine();
    }

    public static function debug(string $message, array $context = [])
    {
        self::$logger->debug($message, $context);
        self::echoLastLine();
    }
}