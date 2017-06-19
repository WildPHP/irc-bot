<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class NICK
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix NICK newnickname
 */
class NICK implements ReceivableMessage, SendableMessage
{
	use PrefixTrait;
	use NicknameTrait;

	protected static $verb = 'NICK';

	/**
	 * @var string
	 */
	protected $newNickname = '';

	public function __construct(string $newNickname)
	{
		$this->setNewNickname($newNickname);
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

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$nickname = $prefix->getNickname();
		$newNickname = $incomingIrcMessage->getArgs()[0];

		$object = new self($newNickname);
		$object->setPrefix($prefix);
		$object->setNickname($nickname);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getNewNickname(): string
	{
		return $this->newNickname;
	}

	/**
	 * @param string $newNickname
	 */
	public function setNewNickname(string $newNickname)
	{
		$this->newNickname = $newNickname;
	}

	public function __toString()
	{
		return 'NICK ' . $this->getNewNickname() . "\r\n";
	}
}