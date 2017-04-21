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

namespace WildPHP\Core\Commands;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\User;

class HelpCommand
{
    public function __construct()
    {
        $commandHelp = new CommandHelp();
        $commandHelp->addPage('Shows the help pages for a specific command.');
        $commandHelp->addPage('Usage: help [command] [page]');
        CommandRegistrar::registerCommand('help', array($this, 'helpCommand'), $commandHelp);
    }

    public function helpCommand(Channel $source, User $user, $args, Queue $queue)
    {
        Logger::debug('Help command called.');

        if (empty($args))
        {
            $args[0] = 'help';
            $args[1] = '1';
        }

        $command = $args[0];
        $page = !empty($args[1]) ? $args[1] : 1; // Take into account arrays starting at position 0.

        if (!GlobalCommandDictionary::getDictionary()->keyExists($command))
        {
            $queue->privmsg($source->getName(), 'That command does not exist, sorry!');
            return;
        }

        $commandObject = GlobalCommandDictionary::getDictionary()[$command];
        $helpObject = $commandObject->getHelp();
        if ($helpObject == null || !($helpObject instanceof CommandHelp))
        {
            $queue->privmsg($source->getName(), 'There is no help available for this command.');
            return;
        }

        $pageToGet = $page - 1;
        if (!$helpObject->indexExists($pageToGet))
        {
            $queue->privmsg($source->getName(), 'That page does not exist for this command.');
            Logger::debug('Tried to grab invalid page from CommandHelp object.', [
                'page' => $pageToGet,
                'object' => $helpObject
            ]);
            return;
        }

        $contents = $helpObject->getPageAt($pageToGet);
        $pageCount = $helpObject->getPageCount();
        $queue->privmsg($source->getName(), $command . ': ' . $contents . ' (page ' . $page . ' of ' . $pageCount . ')');
    }
}