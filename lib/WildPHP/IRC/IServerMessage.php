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
namespace WildPHP\IRC;

/**
 * Represents an inbound server message.
 */
interface IServerMessage
{

	/**
	 * Returns the complete IRC message as it was received from the server.
	 *
	 * @return string Full IRC message.
	 */
	public function getMessage();

	/**
	 * Returns the name of the IRC command that was received.
	 *
	 * @return string The command name.
	 */
	public function getCommand();

	/**
	 * Returns the IRC message params as an array.
	 *
	 * @return array A (possibly empty) array of parameters.
	 */
	public function getParams();

	/**
	 * Returns the IRC message prefix (as defined in RFC 1459 - including the leading colon).
	 * It may be empty.
	 *
	 * @return string The message prefix.
	 */
	public function getPrefix();

	/**
	 * Returns the nickname of the user who sent the message, if available. False otherwise.
	 *
	 * @return string|false
	 */
	public function getNickname();

	/**
	 * Returns the channel parameter or false if not available.
	 *
	 * @return string|false
	 */
	public function getChannel();

	/**
	 * Returns the targets for this message, or false if unavailable.
	 *
	 * @return string|false
	 */
	public function getTargets();

	/**
	 * Gets the code of the message, if available.
	 *
	 * @return string|false
	 */
	public function getCode();

	/**
	 * Returns the IRC message as parsed by Phergie.
	 *
	 * @return array
	 */
	public function get();
}
