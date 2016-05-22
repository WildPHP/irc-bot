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

namespace WildPHP\Core\Events;

class EventEmitter
{
    /**
     * @var \Evenement\EventEmitter
     */
    protected static $emitter = null;
    
    public static function initialize()
    {
        self::$emitter = new \Evenement\EventEmitter();
    }

    public static function on(string $event, callable $listener)
    {
        self::$emitter->on($event, $listener);
    }

    public static function once(string $event, callable $listener)
    {
        self::$emitter->once($event, $listener);
    }

    public static function removeListener(string $event, callable $listener)
    {
        self::$emitter->removeListener($event, $listener);
    }

    public static function removeAllListeners($event = null)
    {
        self::$emitter->removeAllListeners($event);
    }

    public static function listeners(string $event)
    {
        return self::$emitter->listeners($event);
    }

    public static function emit(string $event, array $arguments = [])
    {
        self::$emitter->emit($event, $arguments);
    }
}