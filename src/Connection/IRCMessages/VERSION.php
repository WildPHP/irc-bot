<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

/**
 * Class VERSION
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: VERSION [server]
 */
class VERSION extends BaseIRCMessage implements SendableMessage
{
	protected static $verb = 'VERSION';

	/**
	 * @var string
	 */
	protected $server = '';

	/**
	 * WHOIS constructor.
	 *
	 * @param string $server
	 */
	public function __construct(string $server = '')
	{
		$this->setServer($server);
	}

	/**
	 * @return string
	 */
	public function getServer(): string
	{
		return $this->server;
	}

	/**
	 * @param string $server
	 */
	public function setServer(string $server)
	{
		$this->server = $server;
	}

	public function __toString()
	{
		$server = !empty($this->getServer()) ? ' ' . $this->getServer() : '';
		return 'VERSION' . $server;
	}
}