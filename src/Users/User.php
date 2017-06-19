<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;

use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\ComponentContainer;

class User
{
	/**
	 * @var string
	 */
	protected $nickname = '';

	/**
	 * @var string
	 */
	protected $hostname = '';

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var string
	 */
	protected $ircAccount = '';

	/**
	 * @var ChannelCollection
	 */
	protected $channelCollection;

	/**
	 * User constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$this->channelCollection = new ChannelCollection($container);
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
	public function getNickname(): string
	{
		return $this->nickname;
	}

	/**
	 * @param string $nickname
	 */
	public function setNickname(string $nickname)
	{
		$this->nickname = $nickname;
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
	 * @return ChannelCollection
	 */
	public function getChannelCollection(): ChannelCollection
	{
		return $this->channelCollection;
	}

	/**
	 * @param ChannelCollection $channelCollection
	 */
	public function setChannelCollection(ChannelCollection $channelCollection)
	{
		$this->channelCollection = $channelCollection;
	}
}