<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
	 * @return RAW
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