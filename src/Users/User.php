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

namespace WildPHP\Core\Users;

use WildPHP\Core\Channels\ChannelCollection;

class User
{
	/**
	 * @var string
	 */
	protected $nickname = '';

	/**
	 * @var string
	 */
	protected $ircAccount = '';

	/**
	 * @var ChannelCollection
	 */
	protected $channelCollection;

	public function __construct()
	{
		$this->channelCollection = new ChannelCollection();
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