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
 * Class RPL_NAMREPLY
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: :server 353 nickname visibility channel :nicknames
 */
class RPL_NAMREPLY implements BaseMessage
{
	use NicknameTrait;
	use ChannelTrait;

	protected static $verb = '353';

	protected $nicknames = [];

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

		$args = $incomingIrcMessage->getArgs();
		$nickname = array_shift($args);
		$visibility = array_shift($args);
		$channel = array_shift($args);
		$nicknames = explode(' ', array_shift($args));

		$object = new self();
		$object->setNickname($nickname);
		$object->setChannel($channel);
		$object->setNicknames($nicknames);

		return $object;
	}

	/**
	 * @return array
	 */
	public function getNicknames(): array
	{
		return $this->nicknames;
	}

	/**
	 * @param array $nicknames
	 */
	public function setNicknames(array $nicknames)
	{
		$this->nicknames = $nicknames;
	}
}