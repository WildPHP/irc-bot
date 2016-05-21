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

use WildPHP\Core\Connection\Commands\BaseCommand;

interface QueueInterface
{
    public function insertMessage(BaseCommand $command);
    public function removeMessage(BaseCommand $command);
    
    public function scheduleItem(QueueItem $item);

    //public function pass(string $password);

    //public function nick(string $nickname);

    //public function user(string $username, string $hostname, string $servername, string $realname);

    public function privmsg(string $channel, string $message);

    //public function pong(string $server = '');
}