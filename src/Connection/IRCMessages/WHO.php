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
 * Class WHO
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix WHO nickname/channel options
 */
class WHO extends BaseIRCMessage implements ReceivableMessage, SendableMessage
{
	protected static $verb = 'WHO';

	use PrefixTrait;
	use ChannelTrait;

	/**
	 * @var string
	 */
	protected $options = '';

	/**
	 * WHO constructor.
	 *
	 * @param string $channel
	 * @param string $options
	 */
	public function __construct(string $channel, string $options = '')
	{
		$this->setChannel($channel);
		$this->setOptions($options);
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
		$channel = array_shift($args);
		$options = array_shift($args);

		$object = new self($channel, $options);
		$object->setPrefix($prefix);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getOptions(): string
	{
		return $this->options;
	}

	/**
	 * @param string $options
	 */
	public function setOptions(string $options)
	{
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		$options = $this->getOptions();

		return 'WHO ' . $this->getChannel() . (!empty($options) ? ' ' . $options : '') . "\r\n";
	}
}