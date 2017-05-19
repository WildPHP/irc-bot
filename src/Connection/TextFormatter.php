<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 15:02
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
		'silver' => '15'
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
	public static function color(string $text, string $foreground, string $background = 'white')
	{
		if (!is_numeric($foreground))
			$foreground = self::findColorByString($foreground);

		if (!is_numeric($background))
			$background = self::findColorByString($background);

		return self::$asciiMap['color'] . $foreground . ',' . $background . $text . self::$asciiMap['color'];
	}

	/**
	 * @param string $color
	 *
	 * @return string
	 */
	public static function findColorByString(string $color): string
	{
		$color = strtolower($color);
		if (!array_key_exists($color, self::$colorMap))
			return '00';

		return self::$colorMap[$color];
	}
}