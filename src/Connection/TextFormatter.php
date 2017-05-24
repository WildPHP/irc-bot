<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Connection;


class TextFormatter
{
	// It is required that the numbers are represented as strings (for leading zeroes).
	/**
	 * @var array
	 */
	protected static $colorMap = [
		'white' => '00',
		'black' => '01',
		'blue' => '02',
		'green' => '03',
		'red' => '04',
		'brown' => '05',
		'purple' => '06',
		'orange' => '07',
		'yellow' => '08',
		'lime' => '09',
		'teal' => '10',
		'aqua' => '11',
		'royal' => '12',
		'fuchsia' => '13',
		'grey' => '14',
		'silver' => '15',
	];

	/**
	 * @var array
	 */
	protected static $asciiMap = [
		'bold' => "\x02",
		'underline' => "\x1F",
		'italic' => "\x09",
		'strikethrough' => "\x13",
		'reverse' => "\x16",
		'color' => "\x03"
	];

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function bold(string $text)
	{
		return self::$asciiMap['bold'] . $text . self::$asciiMap['bold'];
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function italic(string $text)
	{
		return self::$asciiMap['italic'] . $text . self::$asciiMap['italic'];
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function underline(string $text)
	{
		return self::$asciiMap['underline'] . $text . self::$asciiMap['underline'];
	}

	/**
	 * @param string $text
	 * @param string $foreground
	 * @param string $background
	 *
	 * @return string
	 */
	public static function color(string $text, string $foreground, string $background = '')
	{
		if (!is_numeric($foreground))
			$foreground = self::findColorByString($foreground);

		if (!is_numeric($background))
			$background = self::findColorByString($background);

		return self::$asciiMap['color'] . $foreground . (!empty($background) ? ',' . $background : '') . $text . self::$asciiMap['color'];
	}

	/**
	 * @param string $color
	 *
	 * @return string
	 */
	public static function findColorByString(string $color): string
	{
		$color = strtolower($color);
		if (empty($color) || !array_key_exists($color, self::$colorMap))
			return '';

		return self::$colorMap[$color];
	}
}