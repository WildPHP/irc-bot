<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 7-5-17
 * Time: 15:03
 */

namespace WildPHP\Core\Connection\IRCMessages;


interface SendableMessage
{
	/**
	 * @return string
	 */
	public function __toString();
}