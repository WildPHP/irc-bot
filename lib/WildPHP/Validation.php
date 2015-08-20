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
 * Validation class, with shortcuts for validating items.
 */
class Validation
{
	/**
	 * Checks if a channel name conforms to RFC2812's grammar rules.
	 * @param string $chan The channel name to check.
	 * @return bool
	 */
	public static function isChannel($chan)
	{
		$pmatch = preg_match('/^(?:\&|\#|\+|\!)[^,\cG ]+$/', $chan);
		return $pmatch !== 0 && $pmatch !== false;
	}

	/**
	 * Checks if a nickname conforms to RFC2812's grammar rules.
	 * @param string $nick The nickname to check.
	 * @return bool
	 */
	public static function isNickname($nick)
	{
		$pmatch = preg_match("/^[^@\n\r ]+$/", $nick);
		return $pmatch !== 0 && $pmatch !== false;
	}
}