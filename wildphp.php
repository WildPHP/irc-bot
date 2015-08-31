<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

use WildPHP\Bot;

// Set error reporting to report all errors
error_reporting(E_ALL);

// Check if we are running as root and quit
if (function_exists('posix_getuid') && posix_getuid() === 0)
{
	echo 'Running wildphp as root is not allowed.' . PHP_EOL;
	exit(128);
}

// Check if we are running high enough PHP version
if (version_compare(PHP_VERSION, '5.4.0', '<'))
{
	echo 'The PHP version you are running (' . PHP_VERSION . ') is not sufficient for WildPHP. Sorry.';
	echo 'Please use PHP 5.3.9 or later.';
	exit(129);
}

// Define global constants
define('WPHP_ROOT_DIR', __DIR__ . '/');
define('WPHP_LIB_DIR', WPHP_ROOT_DIR . 'lib/');
define('WPHP_MODULE_DIR', WPHP_ROOT_DIR . 'modules/');
define('WPHP_LOG_DIR', WPHP_ROOT_DIR . 'logs/');
define('WPHP_CONFIG', WPHP_ROOT_DIR . 'config.neon');

// Turn all PHP errors into exceptions
set_error_handler(
	function($errNo, $errStr, $errFile, $errLine)
	{
		throw new ErrorException($errStr . ' in ' . $errFile . ' on line ' . $errLine, $errNo);
	}
);

// Register the autoloader
require_once('vendor/autoload.php');

// Create a new bot and start it up
$bot = new Bot();
$bot->start();


