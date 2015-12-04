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

namespace WildPHP\Modules\DotModules;

class Parser
{
	/**
	 * @param string $line
	 * @return array|bool Note that false does not mean failure; it can also be a comment!
	 */
	public function processLine($line)
	{
		$line = trim($line);

		if (empty($line) || in_array(substr($line, 0, 1), ['#', ';']))
			return false;

		if (!preg_match('/^(?:(\w+): ?)?(\S+)$/', $line, $matches))
			return false;

		$type = empty($matches[1]) ? 'class' : $matches[1];
		$string = empty($matches[2]) ? $matches[1] : $matches[2];

		return ['type' => $type, 'string' => $string];
	}

	/**
	 * @param string $file
	 * @return array|bool
	 */
	public function readFile($file)
	{
		$lines = file($file);

		if (empty($lines))
			return false;

		$buffer = [];
		foreach ($lines as $line) {
			$result = $this->processLine($line);

			if (empty($result) || in_array($result, $buffer))
				continue;

			$buffer[] = $result;
		}

		return $buffer;
	}

	/**
	 * Safely merge two arrays which might originate from the Parser.
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function mergeSafe($array1, $array2)
	{
		return array_unique(array_merge($array1, $array2), SORT_REGULAR);
	}
}