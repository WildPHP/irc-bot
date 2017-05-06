<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 16:46
 */

namespace WildPHP\Core\Connection\IRCMessages;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class AWAY
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix AWAY :message
 */
class AWAY implements BaseMessage
{
	use PrefixTrait;
	use MessageTrait;

	protected static $verb = 'AWAY';

	public function __construct(string $message)
	{
		$this->setMessage($message);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return AWAY
	 * @throws \ErrorException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);

		$message = $incomingIrcMessage->getArgs()[0];

		$object = new self($message);
		$object->setPrefix($prefix);

		return $object;
	}

	public function __toString()
	{
		return 'AWAY :' . $this->getMessage() . "\r\n";
	}
}