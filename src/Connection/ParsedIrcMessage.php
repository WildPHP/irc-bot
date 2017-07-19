<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

class ParsedIrcMessage
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
}

/**
 * @param $str
 * @param $start
 *
 * @return bool|string
 */
function _substr($str, $start)
{
	$ret = substr($str, $start);

	return $ret === false ? '' : $ret;
}