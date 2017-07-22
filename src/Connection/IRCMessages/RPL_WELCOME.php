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
 * Class RPL_WELCOME
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: :server 001 nickname :greeting
 */
class RPL_WELCOME extends BaseIRCMessage implements ReceivableMessage
{
	use NicknameTrait;
	use ServerTrait;

	protected static $verb = '001';

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

		$nickname = $incomingIrcMessage->getArgs()[0];
		$server = $incomingIrcMessage->getPrefix();
		$object = new self();
		$object->setNickname($nickname);
		$object->setServer($server);

		return $object;
	}
}