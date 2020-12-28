<?php

/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    echo 'WildPHP must be run from the terminal!' . PHP_EOL;
    exit(127);
}

/** @noinspection PhpComposerExtensionStubsInspection */
if (function_exists('posix_getuid') && posix_getuid() === 0) {
    echo 'Running wildphp as root is not allowed.' . PHP_EOL;
    exit(128);
}

if (PHP_VERSION_ID < 70300) {
    echo 'The PHP version you are running (' . PHP_VERSION . ') is not sufficient for WildPHP. Sorry.';
    echo 'Please use PHP 7.3.0 or later.';
    exit(129);
}
include dirname(__DIR__) . '/app/bootstrap.php';
