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

/**
 * A class used as a base for all managers.
 */
abstract class Manager
{

	/**
	 * Instance of the bot.
	 * @var Bot
	 */
	protected $bot;

	/**
	 * Sets up the module manager.
	 * @param Bot $bot An instance of the bot this manager is running under.
	 */
	public function __construct(Bot $bot)
	{
		$this->bot = $bot;
	}

	/**
	 * Sends a message to the log.
	 * @param string $message the message to be logged.
	 */
	protected function log($message)
	{
		$this->bot->log($message, __CLASS__);
	}

	/**
	 * Sends a message to the debug log.
	 * @todo Implement this properly when the log manager is changed.
	 * @param string $message the message to be logged.
	 */
	protected function logDebug($message)
	{
		$this->bot->log($message, __CLASS__ . '-DEBUG');
	}
}
