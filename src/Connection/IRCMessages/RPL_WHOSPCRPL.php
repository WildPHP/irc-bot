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
 * Class RPL_WHOSPCRPL
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax (as used by WildPHP): :server 354 ownnickname username hostname nickname status accountname
 */
class RPL_WHOSPCRPL extends BaseIRCMessage implements ReceivableMessage
{
	use NicknameTrait;
	use ChannelTrait;
	use MessageTrait;
	use ServerTrait;

	protected static $verb = '354';

	/**
	 * @var string
	 */
	protected $ownNickname = '';

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var string
	 */
	protected $hostname = '';

	/**
	 * @var string
	 */
	protected $status = '';

	/**
	 * @var string
	 */
	protected $accountname = '';

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

		$server = $incomingIrcMessage->getPrefix();
		$args = $incomingIrcMessage->getArgs();
		$ownNickname = array_shift($args);
		$username = array_shift($args);
		$hostname = array_shift($args);
		$nickname = array_shift($args);
		$status = array_shift($args);
		$accountname = array_shift($args);

		$object = new self();
		$object->setOwnNickname($ownNickname);
		$object->setUsername($username);
		$object->setHostname($hostname);
		$object->setNickname($nickname);
		$object->setStatus($status);
		$object->setAccountname($accountname);
		$object->setServer($server);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getOwnNickname(): string
	{
		return $this->ownNickname;
	}

	/**
	 * @param string $ownNickname
	 */
	public function setOwnNickname(string $ownNickname)
	{
		$this->ownNickname = $ownNickname;
	}

	/**
	 * @return string
	 */
	public function getUsername(): string
	{
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername(string $username)
	{
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getHostname(): string
	{
		return $this->hostname;
	}

	/**
	 * @param string $hostname
	 */
	public function setHostname(string $hostname)
	{
		$this->hostname = $hostname;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus(string $status)
	{
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getAccountname(): string
	{
		return $this->accountname;
	}

	/**
	 * @param string $accountname
	 */
	public function setAccountname(string $accountname)
	{
		$this->accountname = $accountname;
	}
}