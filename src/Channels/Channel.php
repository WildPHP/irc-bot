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

use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\GlobalUserCollection;
use WildPHP\Core\Users\User;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;
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
	 * Stored as 'mode' => [User, User, User, ...]
	 * @var array
	 */
	protected $modeMap = [];

	public function __construct()
	{
		$this->userCollection = new UserCollection();

		EventEmitter::on('user.join', [$this, 'updateParticipatingUsers']);
		EventEmitter::on('user.part', [$this, 'removeUser']);
		EventEmitter::on('user.quit', [$this, 'removeUser']);
		EventEmitter::on('user.nick', [$this, 'updateUserNickname']);
		EventEmitter::on('irc.line.in.353', [$this, 'updateInitialParticipatingUsers']);
	}

	/**
	 * @param User $user
	 */
	public function removeUser(User $user)
	{
		if (!$this->getUserCollection()->isUserInCollection($user))
			return;

		$this->getUserCollection()->removeUser($user);
		$user->getChannelCollection()->removeChannel($this);

	}

	/**
	 * @param $oldNickname
	 * @param $newNickname
	 * @param Queue $queue
	 */
	public function updateUserNickname($oldNickname, $newNickname, Queue $queue)
	{
		$userObject = $this->getUserCollection()->findUserByNickname($oldNickname);

		if ($userObject == false)
			return;

		$this->getUserCollection()->removeUserByNickname($oldNickname);
		$this->getUserCollection()->addUser($userObject);
	}

	/**
	 * @param User $user
	 * @param string $channel
	 */
	public function updateParticipatingUsers(User $user, string $channel)
	{
		if ($channel != $this->getName())
			return;

		$this->getUserCollection()->addUser($user);
		$user->getChannelCollection()->addChannel($this);
	}

	// TODO refactor this
	/**
	 * @param IncomingIrcMessage $message
	 * @param Queue $queue
	 */
	public function updateInitialParticipatingUsers(IncomingIrcMessage $message, Queue $queue)
	{
		$args = $message->getArgs();
		$channel = $args[2];
		$users = explode(' ', $args[3]);

		if ($channel != $this->getName())
			return;

		if (empty(ChannelDataCollector::$modeMap))
			ChannelDataCollector::createModeMap();

		foreach ($users as $user)
		{
			$firstChar = substr($user, 0, 1);
			$nickname = $user;
			if (array_key_exists($firstChar, ChannelDataCollector::$modeMap))
			{
				$key = ChannelDataCollector::$modeMap[$firstChar];
				$nickname = substr($user, 1);
				$userObject = GlobalUserCollection::findOrCreateUserObject($nickname);
				$this->modeMap[$key][] = $userObject;
			}

			$userObject = GlobalUserCollection::findOrCreateUserObject($nickname);
			$this->updateParticipatingUsers($userObject, $this->getName());
		}
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
	public function setUserCollection(UserCollection $userCollection)
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
}