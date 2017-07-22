<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 22-7-2017
 * Time: 15:40
 */

namespace WildPHP\Core\Connection\IRCMessages;


/**
 * Class WHOIS
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: WHOIS ([server]) [nickname] ([nickname] ...)
 */
class WHOIS extends BaseIRCMessage implements SendableMessage
{
	protected static $verb = 'WHOIS';

	/**
	 * @var string
	 */
	protected $server = '';

	/**
	 * @var string[]
	 */
	protected $nicknames = '';

	/**
	 * WHOIS constructor.
	 *
	 * @param string[]|string $nicknames
	 * @param string $server
	 */
	public function __construct($nicknames, string $server = '')
	{
		if (is_string($nicknames))
			$nicknames = [$nicknames];

		$this->setNicknames($nicknames);
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

	/**
	 * @return string[]
	 */
	public function getNicknames()
	{
		return $this->nicknames;
	}

	/**
	 * @param string[] $nicknames
	 */
	public function setNicknames($nicknames)
	{
		$this->nicknames = $nicknames;
	}

	public function __toString()
	{
		$server = !empty($this->getServer()) ? $this->getServer() . ' ' : '';
		return 'WHOIS ' . $server . implode(',', $this->getNicknames());
	}
}