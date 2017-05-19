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
 * Class CAP
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix CAP nickname command [:capabilities]
 */
class CAP implements BaseMessage, SendableMessage
{
	protected static $verb = 'CAP';

	use NicknameTrait;

	/**
	 * @var string
	 */
	protected $command = '';

	/**
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * CAP constructor.
	 *
	 * @param string $command
	 * @param array $capabilities
	 */
	public function __construct(string $command, array $capabilities = [])
	{
		$this->setCommand($command);
		$this->setCapabilities($capabilities);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return \self
	 * @throws \InvalidArgumentException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

		$args = $incomingIrcMessage->getArgs();
		$nickname = array_shift($args);
		$command = array_shift($args);
		$capabilities = explode(' ', array_shift($args));

		$object = new self($command, $capabilities);
		$object->setNickname($nickname);

		return $object;
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
	 * @return array
	 */
	public function getCapabilities(): array
	{
		return $this->capabilities;
	}

	/**
	 * @param array $capabilities
	 */
	public function setCapabilities(array $capabilities)
	{
		$this->capabilities = $capabilities;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		$capabilities = implode(' ', $this->getCapabilities());

		return 'CAP ' . $this->getCommand() . (!empty($capabilities) ? ' :' . $capabilities : '') . "\r\n";
	}
}