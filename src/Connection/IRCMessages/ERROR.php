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
 * Class ERROR
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: ERROR :message
 */
class ERROR implements ReceivableMessage
{
	use MessageTrait;

	protected static $verb = 'ERROR';

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

		$message = $incomingIrcMessage->getArgs()[0];
		$object = new self();
		$object->setMessage($message);

		return $object;
	}
}