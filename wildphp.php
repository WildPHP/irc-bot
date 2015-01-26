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

namespace WildPHP;
use WildPHP\Core\Bot;

// Check if we are running as root and quit
if(function_exists('posix_getuid()') && posix_getuid() === 0)
{
	echo 'Running wildphp as root is not allowed.';
	exit 128;
}

// Define global constants
define('WPHP_ROOT_DIR', __DIR__ . '/');
define('WPHP_MODULE_DIR', WPHP_ROOT_DIR . 'modules/');
define('WPHP_LOG_DIR', WPHP_ROOT_DIR . 'logs/');
define('WPHP_CONFIG', WPHP_ROOT_DIR . 'config.neon');


// Register the autoloader
require_once(WPHP_ROOT_DIR . 'core/Autoloader.php');
spl_autoload_register('WildPHP\Core\Autoloader::load');

$bot = new Bot();
