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
		'bold' => 0x02,
		'color' => 0x03,
		'italic' => 0x1D,
		'underline' => 0x1F,
		'reverse' => 0x16,
		'reset' => 0x0F
	];

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function bold(string $text)
	{
		return chr(self::$asciiMap['bold']) . $text . chr(self::$asciiMap['bold']);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function italic(string $text)
	{
		return chr(self::$asciiMap['italic']) . $text . chr(self::$asciiMap['italic']);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function underline(string $text)
	{
		return chr(self::$asciiMap['underline']) . $text . chr(self::$asciiMap['underline']);
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

		return chr(self::$asciiMap['color']) . $foreground . ',' . $background . $text . chr(self::$asciiMap['color']);
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