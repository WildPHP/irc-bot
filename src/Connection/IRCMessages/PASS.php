<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

/**
 * Class PASS
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: PASS password
 */
class PASS extends BaseIRCMessage implements SendableMessage
{
	protected static $verb = 'PASS';

	protected $password = '';

	/**
	 * PASS constructor.
	 *
	 * @param string $password
	 */
	public function __construct(string $password)
	{
		$this->setPassword($password);
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword(string $password)
	{
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'PASS :' . $this->getPassword() . "\r\n";
	}
}