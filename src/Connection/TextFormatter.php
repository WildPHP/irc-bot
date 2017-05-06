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
	// It is required that the numbers are represented as strings.
	protected $colorMap = [
		'white' => '00',
		'black' => '01',
		'blue' => '02',
		'green' => '03',
		'red' => '04',
		'brown' => '05',
		'purple' => '06',
		'orange' => '07',
		'yellow' => '08',
	];
}