<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 15:58
 */

namespace WildPHP\Core\Connection\IRCMessages;


use WildPHP\Core\Connection\UserPrefix;

trait PrefixTrait
{
	/**
	 * @var UserPrefix
	 */
	protected $prefix = null;

	/**
	 * @return UserPrefix
	 */
	public function getPrefix(): UserPrefix
	{
		return $this->prefix;
	}

	/**
	 * @param UserPrefix $prefix
	 */
	public function setPrefix(UserPrefix $prefix)
	{
		$this->prefix = $prefix;
	}
}