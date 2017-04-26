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

use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\UserCollection;

class Channel
{
	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $topic = '';

	/**
	 * @var UserCollection
	 */
	protected $userCollection;

	/**
	 * @var ChannelModes
	 */
	protected $channelModes;

	public function __construct()
	{
		$this->userCollection = new UserCollection();
		$this->channelModes = new ChannelModes();
	}

	public function __destruct()
	{
		$this->setUserCollection(null);
		$this->setChannelModes(null);
		Logger::debug('Channel object destructed.', ['name' => $this->getName()]);
	}

	/**
	 * @return ChannelModes
	 */
	public function getChannelModes(): ChannelModes
	{
		return $this->channelModes;
	}

	/**
	 * @param ChannelModes $channelModes
	 */
	public function setChannelModes(?ChannelModes $channelModes)
	{
		$this->channelModes = $channelModes;
	}

	/**
	 * @return UserCollection
	 */
	public function getUserCollection(): UserCollection
	{
		return $this->userCollection;
	}

	/**
	 * @param UserCollection $userCollection
	 */
	public function setUserCollection(?UserCollection $userCollection)
	{
		$this->userCollection = $userCollection;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name)
	{
		$this->name = $name;
	}

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

	/**
	 * @param string $name
	 * @return bool
	 */
	public static function isValidName(string $name)
	{
		$prefix = Configuration::get('serverConfig.chantypes')->getValue();
		return substr($name, 0, strlen($prefix)) == $prefix;
	}
}