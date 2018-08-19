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
 * Class ACCOUNT
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix ACCOUNT accountname
 */
class ACCOUNT extends BaseIRCMessage implements ReceivableMessage
{
	protected static $verb = 'ACCOUNT';

	use PrefixTrait;

	/**
	 * @var string
	 */
	protected $accountName = '';

	/**
	 * ACCOUNT constructor.
	 *
	 * @param string $accountName
	 */
	function __construct(string $accountName)
	{
		$this->setAccountName($accountName);
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

		$accountName = $incomingIrcMessage->getArgs()[0];
		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);

		$object = new self($accountName);
		$object->setPrefix($prefix);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getAccountName(): string
	{
		return $this->accountName;
	}

	/**
	 * @param string $accountName
	 */
	public function setAccountName(string $accountName)
	{
		$this->accountName = $accountName;
	}
}