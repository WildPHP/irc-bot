<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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