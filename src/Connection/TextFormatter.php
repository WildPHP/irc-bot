<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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
		return self::$colorMap[$color] ?? '';
	}

	/**
	 * @param string $stringToColor
	 *
	 * @return string
	 */
	public static function calculateStringColor(string $stringToColor): string
	{
		$num = 0;
		foreach (str_split($stringToColor) as $char)
		{
			$num += ord($char);
		}
		// The -1 is here to account for index 0
		return abs($num) % (count(self::$colorMap) - 1);
	}

	/**
	 * @param string $stringToColor
	 * @param string $background
	 *
	 * @return string
	 */
	public static function consistentStringColor(string $stringToColor, string $background = ''): string
	{
		// Don't even bother.
		if (empty($stringToColor))
			return '';

		$color = self::calculateStringColor($stringToColor);
		return self::color($stringToColor, $color, $background);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function stripBold(string $text): string
	{
		return str_replace(self::$asciiMap['bold'], '', $text);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function stripItalic(string $text): string
	{
		return str_replace(self::$asciiMap['italic'], '', $text);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function stripUnderline(string $text): string
	{
		return str_replace(self::$asciiMap['underline'], '', $text);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function stripColor(string $text): string
	{
		$regex = '/' . preg_quote(self::$asciiMap['color']) . '(\d{1,2},\d{1,2})?/';
		return preg_replace($regex, '', $text);
	}
}