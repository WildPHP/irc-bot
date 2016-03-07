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

use WildPHP\Core\Configuration\Configuration;

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

if (version_compare(PHP_VERSION, '7.0.0', '<'))
{
	echo 'The PHP version you are running (' . PHP_VERSION . ') is not sufficient for WildPHP. Sorry.';
	echo 'Please use PHP 7.0.0 or later.';
	exit(129);
}
require('vendor/autoload.php');
define("WPHP_ROOT_DIR", __DIR__ . '/');

Configuration::initialize();

var_dump(Configuration::get('connections'));

Configuration::writeAll();