<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 16:46
 */

namespace WildPHP\Core\Connection\IRCMessages;
use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Class ERROR
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: ERROR :message
 */
class ERROR implements BaseMessage
{
	use MessageTrait;

	protected static $verb = 'ERROR';

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return ERROR
	 * @throws \ErrorException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

		$message = $incomingIrcMessage->getArgs()[0];
		$object = new self();
		$object->setMessage($message);

		return $object;
	}
}