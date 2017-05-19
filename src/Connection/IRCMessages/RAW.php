<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 27-5-16
 * Time: 19:25
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Class RAW
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix RAW nickname/channel options
 */
class RAW implements BaseMessage, SendableMessage
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