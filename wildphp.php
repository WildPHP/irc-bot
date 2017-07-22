<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

error_reporting(E_ALL);

if (php_sapi_name() != 'cli')
{
	echo 'WildPHP must be run from the terminal!';
	exit(127);
}

if (function_exists('posix_getuid') && posix_getuid() === 0)
{
	echo 'Running wildphp as root is not allowed.' . PHP_EOL;
	exit(128);
}

if (version_compare(PHP_VERSION, '7.1.0', '<'))
{
	echo 'The PHP version you are running (' . PHP_VERSION . ') is not sufficient for WildPHP. Sorry.';
	echo 'Please use PHP 7.1.0 or later.';
	exit(129);
}
require('vendor/autoload.php');
define('WPHP_ROOT_DIR', __DIR__ . '/');
define('WPHP_VERSION', '3.0.0');

include(WPHP_ROOT_DIR . 'src/bootstrap.php');