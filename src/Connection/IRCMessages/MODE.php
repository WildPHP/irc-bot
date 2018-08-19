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
 * Class MODE
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax (initial): nickname MODE nickname :modes
 * Syntax (user): prefix MODE nickname flags
 * Syntax (channel): prefix MODE #channel flags [arguments]
 */
class MODE extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	use PrefixTrait;
	use NicknameTrait;

	/**
	 * @var string
	 */
	protected static $verb = 'MODE';

	/**
	 * @var string
	 */
	protected $flags = '';

	/**
	 * @var string
	 */
	protected $target = '';

	/**
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * MODE constructor.
	 *
	 * @param string $target
	 * @param string $flags
	 * @param array $arguments
	 */
	public function __construct(string $target, string $flags, array $arguments = [])
	{
		$this->setTarget($target);
		$this->setFlags($flags);
		$this->setArguments($arguments);
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
		$target = array_shift($args);
		$flags = array_shift($args);

		$object = new self($target, $flags, $args);
		$object->setPrefix($prefix);
		$object->setNickname($prefix->getNickname());

		return $object;
	}

	/**
	 * @return string
	 */
	public function getFlags(): string
	{
		return $this->flags;
	}

	/**
	 * @param string $flags
	 */
	public function setFlags(string $flags)
	{
		$this->flags = $flags;
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
	 * @return array
	 */
	public function getArguments(): array
	{
		return $this->arguments;
	}

	/**
	 * @param array $arguments
	 */
	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$arguments = implode(' ', $this->getArguments());

		return 'MODE ' . $this->getTarget() . ' ' . $this->getFlags() . ' ' . $arguments . "\r\n";
	}
}