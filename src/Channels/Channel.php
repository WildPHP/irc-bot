<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Channels;

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
	 * @var string
	 */
	protected $createdBy = '';

	/**
	 * @var int
	 */
	protected $createdTime = 0;

	/**
	 * @var UserCollection
	 */
	protected $userCollection;

	/**
	 * @var ChannelModes
	 */
	protected $channelModes;

	/**
	 * Channel constructor.
	 *
	 * @param string $name
	 * @param UserCollection $userCollection
	 * @param ChannelModes $channelModes
	 */
	public function __construct(string $name, UserCollection $userCollection, ChannelModes $channelModes)
	{
		$this->name = $name;
		$this->setUserCollection($userCollection);
		$this->setChannelModes($channelModes);
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
	public function setChannelModes(ChannelModes $channelModes)
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
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @param mixed $createdBy
	 */
	public function setCreatedBy($createdBy)
	{
		$this->createdBy = $createdBy;
	}

	/**
	 * @return int
	 */
	public function getCreatedTime(): int
	{
		return $this->createdTime;
	}

	/**
	 * @param int $createdTime
	 */
	public function setCreatedTime(int $createdTime)
	{
		$this->createdTime = $createdTime;
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
	 * @param string $prefix
	 *
	 * @return bool
	 */
	public static function isValidName(string $name, string $prefix)
	{
		return substr($name, 0, strlen($prefix)) == $prefix;
	}


}