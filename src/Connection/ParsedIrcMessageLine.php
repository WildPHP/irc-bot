<?php

/*
 * Original source: https://github.com/grawity/hacks
 *
 * Licensed under the MIT Expat license:
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace WildPHP\Core\Connection;

class ParsedIrcMessageLine
{
	/**
	 * @var array
	 */
	public $tags = [];
	/**
	 * @var string
	 */
	public $prefix = null;
	/**
	 * @var string
	 */
	public $verb = null;
	/**
	 * @var array
	 */
	public $args = [];

	/**
	 * @param $line
	 *
	 * @return array
	 */
	public static function split($line)
	{
		$line = rtrim($line, "\r\n");
		$line = explode(' ', $line);
		$index = 0;
		$arv_count = count($line);
		$parv = [];

		while ($index < $arv_count && $line[$index] === '')
		{
			$index++;
		}

		if ($index < $arv_count && $line[$index][0] == '@')
		{
			$parv[] = $line[$index];
			$index++;
			while ($index < $arv_count && $line[$index] === '')
			{
				$index++;
			}
		}

		if ($index < $arv_count && $line[$index][0] == ':')
		{
			$parv[] = $line[$index];
			$index++;
			while ($index < $arv_count && $line[$index] === '')
			{
				$index++;
			}
		}

		while ($index < $arv_count)
		{
			if ($line[$index] === '')
				;
			elseif ($line[$index][0] === ':')
				break;
			else
				$parv[] = $line[$index];
			$index++;
		}

		if ($index < $arv_count)
		{
			$trailing = implode(' ', array_slice($line, $index));
			$parv[] = _substr($trailing, 1);
		}

		return $parv;
	}

	/**
	 * @param $line
	 *
	 * @return ParsedIrcMessageLine
	 */
	public static function parse($line)
	{
		$parv = self::split($line);
		$index = 0;
		$parv_count = count($parv);
		$self = new self();

		if ($index < $parv_count && $parv[$index][0] === '@')
		{
			$tags = _substr($parv[$index], 1);
			$index++;
			foreach (explode(';', $tags) as $item)
			{
				list($k, $v) = explode('=', $item, 2);
				if ($v === null)
					$self->tags[$k] = true;
				else
					$self->tags[$k] = $v;
			}
		}

		if ($index < $parv_count && $parv[$index][0] === ':')
		{
			$self->prefix = _substr($parv[$index], 1);
			$index++;
		}

		if ($index < $parv_count)
		{
			$self->verb = strtoupper($parv[$index]);
			$self->args = array_slice($parv, $index);
		}

		return $self;
	}
}

/**
 * @param $str
 * @param $start
 * @return bool|string
 */
function _substr($str, $start)
{
	$ret = substr($str, $start);

	return $ret === false ? '' : $ret;
}