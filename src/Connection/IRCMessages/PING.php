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
 * Class PING
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: PING server1 [server2]
 */
class PING implements BaseMessage
{
	protected static $verb = 'PING';

	protected $server1 = '';

	protected $server2 = '';

	public function __construct(string $server1, string $server2 = '')
	{
		$this->setServer1($server1);
		$this->setServer2($server2);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return PING
	 * @throws \ErrorException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

		$args = $incomingIrcMessage->getArgs();
		$server1 = $args[0];
		$server2 = $args[1] ?? '';

		return new self($server1, $server2);
	}

	/**
	 * @return string
	 */
	public function getServer1(): string
	{
		return $this->server1;
	}

	/**
	 * @param string $server1
	 */
	public function setServer1(string $server1)
	{
		$this->server1 = $server1;
	}

	/**
	 * @return string
	 */
	public function getServer2(): string
	{
		return $this->server2;
	}

	/**
	 * @param string $server2
	 */
	public function setServer2(string $server2)
	{
		$this->server2 = $server2;
	}

	public function __toString()
	{
		$server2 = $this->getServer2();
		return 'PING ' . $this->getServer1() . (!empty($server2) ? ' ' . $server2 : '') . "\r\n";
	}
}