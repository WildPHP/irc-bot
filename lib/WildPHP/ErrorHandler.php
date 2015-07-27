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

use WildPHP\LogManager\LogLevels;

class ErrorHandler extends Manager
{
    /**
     * Setup the Error Handler.
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        parent::__construct($bot);

        set_error_handler(array($this, 'handler'));
    }

    /**
     * Handle errors.
     * @param int $errno The error number.
     * @param string $errstr The error message.
     * @param string $errfile The error file.
     * @param string $errline The line the error occured.
     * @return bool False to jump to the regular error handler.
     */
    public function handler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno)
        {
            case E_USER_ERROR:
            case E_ERROR:
                $level = LogLevels::ERROR;
                $this->bot->log('{string} on line {line} in file {file}', array('string' => $errstr, 'line' => $errline, 'file' => $errfile), $level);
                exit(1);
                break;

            case E_USER_WARNING:
            case E_WARNING:
                $level = LogLevels::WARNING;
                break;

            case E_USER_NOTICE:
            case E_NOTICE:
                $level = LogLevels::INFO;
                break;

            default:
                $level = LogLevels::DEBUG;
                break;
        }

        $this->bot->log('{string} on line {line} in file {file}', array('string' => $errstr, 'line' => $errline, 'file' => $errfile), $level);
        return true;
    }
}