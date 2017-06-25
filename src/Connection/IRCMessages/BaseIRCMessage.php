<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


abstract class BaseIRCMessage
{
	/**
	 * @var string
	 */
	protected static $verb;

	/**
	 * @return string
	 */
	public static function getVerb(): string
	{
		return static::$verb;
	}
}