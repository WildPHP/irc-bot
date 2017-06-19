<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Class RAW
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix RAW nickname/channel options
 */
class RAW implements ReceivableMessage, SendableMessage
{
	/**
	 * @var string
	 */
	protected $command;

	/**
	 * RAW constructor.
	 *
	 * @param string $command
	 */
	public function __construct(string $command)
	{
		$this->setCommand($command);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return \self
	 * @throws \ErrorException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		throw new \ErrorException('You could not have received this message... :o');
	}

	/**
	 * @return string
	 */
	public function getCommand(): string
	{
		return $this->command;
	}

	/**
	 * @param string $command
	 */
	public function setCommand(string $command)
	{
		$this->command = $command;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getCommand() . "\r\n";
	}
}