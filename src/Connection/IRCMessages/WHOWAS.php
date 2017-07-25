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
 * Class WHOWAS
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: WHOIS nickname(,nickname,...) (count] (server))
 */
class WHOWAS extends BaseIRCMessage implements SendableMessage
{
	protected static $verb = 'WHOIS';

	use ServerTrait;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var string[]
	 */
	protected $nicknames = '';

	/**
	 * WHOWAS constructor.
	 *
	 * @param string[]|string $nicknames
	 * @param int $count
	 * @param string $server
	 */
	public function __construct($nicknames, int $count = 0, string $server = '')
	{
		if (is_string($nicknames))
			$nicknames = [$nicknames];

		$this->setNicknames($nicknames);
		$this->setCount($count);
		$this->setServer($server);
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

	/**
	 * @return int
	 */
	public function getCount(): int
	{
		return $this->count;
	}

	/**
	 * @param int $count
	 */
	public function setCount(int $count)
	{
		$this->count = $count;
	}

	public function __toString()
	{
		$count = !empty($this->getCount()) ? ' ' . trim($this->getCount() . ' ' . $this->getServer()) : '';
		return 'WHOWAS ' .  implode(',', $this->getNicknames()) . $count;
	}
}