<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

/**
 * Class RAW
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix RAW nickname/channel options
 */
class RAW extends BaseIRCMessage implements SendableMessage
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