<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class CAP
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix CAP nickname command [:capabilities]
 */
class CAP extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	protected static $verb = 'CAP';

	use PrefixTrait;
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
		if (!in_array($command, ['LS', 'LIST', 'REQ', 'ACK', 'NAK', 'END']))
			throw new \InvalidArgumentException('CAP subcommand not valid');

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
		if ($incomingIrcMessage->getVerb() != self::getVerb())
			throw new \InvalidArgumentException('Expected incoming ' . self::getVerb() . '; got ' . $incomingIrcMessage->getVerb());

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$args = $incomingIrcMessage->getArgs();
		$nickname = array_shift($args);
		$command = array_shift($args);
		$capabilities = explode(' ', array_shift($args));

		$object = new self($command, $capabilities);
		$object->setNickname($nickname);
		$object->setPrefix($prefix);

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