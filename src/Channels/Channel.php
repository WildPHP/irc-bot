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

namespace WildPHP\Core\Channels;

use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Events\EventEmitter;

class Channel
{
	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * Stored as 'nickname' => 'mode'
	 * @var array
	 */
	protected $modeMap = [];

	public function __construct()
	{
		EventEmitter::on('irc.line.in.nick', array($this, 'updateNicknames'));
	}

	public function setUserMode(string $user, string $mode)
	{
		$this->modeMap[$user] = $mode;
	}

	public function getUserMode(string $user)
	{
		if (!array_key_exists($user, $this->modeMap))
			return null;

		return $this->modeMap[$user];
	}

	public function updateNicknames(IncomingIrcMessage $incomingIrcMessage)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$oldNickname = explode('!', $prefix)[0];
		$newNickname = $incomingIrcMessage->getArgs()[0];

		if (array_key_exists($oldNickname, $this->modeMap))
		{
			$this->modeMap[$newNickname] = $this->modeMap[$oldNickname];
			unset($this->modeMap[$oldNickname]);
		}
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $topic = '';

	/**
	 * @return string
	 */
	public function getTopic(): string
	{
		return $this->topic;
	}

	/**
	 * @param string $topic
	 */
	public function setTopic(string $topic)
	{
		$this->topic = $topic;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description)
	{
		$this->description = $description;
	}
}