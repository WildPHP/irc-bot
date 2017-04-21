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


use WildPHP\Core\Logger\Logger;

class CommandRegistrar
{
	/**
	 * @param string $command
	 * @param callable $callback
	 *
	 * @throws CommandAlreadyExistsException
	 */
	public static function registerCommand(string $command, callable $callback, CommandHelp $commandHelp = null)
	{
		if (GlobalCommandDictionary::getDictionary()->keyExists($command))
			throw new CommandAlreadyExistsException();

        $commandObject = new Command();
        $commandObject->setCallback($callback);

        if (!is_null($commandHelp))
            $commandObject->setHelp($commandHelp);

		GlobalCommandDictionary::getDictionary()[$command] = $commandObject;
	}

	/**
	 * @return array
	 */
	public static function listCommands(): array
	{
		return array_keys(GlobalCommandDictionary::getDictionary()->toArray());
	}

	/**
	 * @param string $command
	 *
	 * @throws CommandDoesNotExistException
	 */
	public static function deregisterCommand(string $command)
	{
		if (!GlobalCommandDictionary::getDictionary()->keyExists($command))
			throw new CommandDoesNotExistException();

		GlobalCommandDictionary::getDictionary()->offsetUnset($command);
	}
}