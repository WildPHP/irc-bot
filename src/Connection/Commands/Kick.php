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

namespace WildPHP\Core\Connection\Commands;


class Kick extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var string
	 */
	protected $channelName;

	/**
	 * @var string
	 */
	protected $nickname;

	/**
	 * @param string $channelName
	 * @param string $nickname
	 * @param string $message
	 */
	public function __construct(string $channelName, string $nickname, string $message = '')
	{
		$this->setChannelName($channelName);
		$this->setNickname($nickname);
		$this->setMessage($message);
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage(string $message)
	{
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function getChannelName(): string
	{
		return $this->channelName;
	}

	/**
	 * @param string $channelName
	 */
	public function setChannelName(string $channelName)
	{
		$this->channelName = $channelName;
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
	public function formatMessage(): string
	{
		$message = $this->getMessage();
		$nickname = $this->getNickname();
		$channel = $this->getChannelName();
		if (empty($message))
			$message = $nickname;

		return 'KICK ' . $channel . ' ' . $nickname . ' :' . $message . "\r\n";
	}
}