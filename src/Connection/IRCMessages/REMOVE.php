<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


/**
 * Class REMOVE
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix REMOVE #channel nickname :message
 */
class REMOVE extends BaseIRCMessage implements SendableMessage
{
	use ChannelTrait;
	use PrefixTrait;
	use NicknameTrait;
	use MessageTrait;

	protected static $verb = 'REMOVE';

	/**
	 * @var string
	 */
	protected $target = '';

	/**
	 * REMOVE constructor.
	 *
	 * @param string $channel
	 * @param string $nickname
	 * @param string $message
	 */
	public function __construct(string $channel, string $nickname, string $message)
	{
		$this->setChannel($channel);
		$this->setTarget($nickname);
		$this->setMessage($message);
	}

	/**
	 * @return string
	 */
	public function getTarget(): string
	{
		return $this->target;
	}

	/**
	 * @param string $target
	 */
	public function setTarget(string $target)
	{
		$this->target = $target;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'REMOVE ' . $this->getChannel() . ' ' . $this->getTarget() . ' :' . $this->getMessage() . "\r\n";
	}
}