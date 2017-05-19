<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Connection\IRCMessages;


use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class JOIN
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax (extended-join): prefix JOIN #channel accountName :realname
 * Syntax (regular): prefix JOIN #channel
 * Syntax (sender): JOIN #channels [keys]
 */
class JOIN implements BaseMessage, SendableMessage
{
	use ChannelsTrait;
	use NicknameTrait;
	use PrefixTrait;

	/**
	 * @var string
	 */
	protected static $verb = 'JOIN';

	/**
	 * @var string
	 */
	protected $ircAccount = '';

	/**
	 * @var string
	 */
	protected $realname = '';

	/**
	 * @var array
	 */
	protected $keys = [];

	/**
	 * JOIN constructor.
	 *
	 * @param array|string $channels
	 * @param array|string $keys
	 */
	public function __construct($channels, $keys = [])
	{
		if (!is_array($channels))
			$channels = [$channels];

		if (!is_array($keys))
			$keys = [$keys];

		if (!empty($keys) && count($channels) != count($keys))
			throw new \InvalidArgumentException('Channel and key count mismatch');

		$this->setChannels($channels);
		$this->setKeys($keys);
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
		$args = $incomingIrcMessage->getArgs();
		$channel = $args[0];
		$ircAccount = $args[1] ?? '';
		$realname = $args[2] ?? '';

		$object = new self($channel);
		$object->setPrefix($prefix);
		$object->setNickname($prefix->getNickname());
		$object->setIrcAccount($ircAccount);
		$object->setRealname($realname);

		return $object;
	}

	/**
	 * @return string
	 */
	public function getIrcAccount(): string
	{
		return $this->ircAccount;
	}

	/**
	 * @param string $ircAccount
	 */
	public function setIrcAccount(string $ircAccount)
	{
		$this->ircAccount = $ircAccount;
	}

	/**
	 * @return string
	 */
	public function getRealname(): string
	{
		return $this->realname;
	}

	/**
	 * @param string $realname
	 */
	public function setRealname(string $realname)
	{
		$this->realname = $realname;
	}

	/**
	 * @return array
	 */
	public function getKeys(): array
	{
		return $this->keys;
	}

	/**
	 * @param array $keys
	 */
	public function setKeys(array $keys)
	{
		$this->keys = $keys;
	}

	public function __toString()
	{
		$channels = implode(',', $this->getChannels());
		$keys = implode(',', $this->getKeys());

		return 'JOIN ' . $channels . (!empty($keys) ? ' ' . $keys : '') . "\r\n";
	}
}