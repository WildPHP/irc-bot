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

use WildPHP\Core\Users\GlobalUserCollection;
use WildPHP\Core\Users\User;
use WildPHP\Core\Connection\IncomingIrcMessage;
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
		EventEmitter::on('user.mode', [$this, 'updateUserMode']);
		EventEmitter::on('irc.line.in.353', [$this, 'updateInitialParticipatingUsers']);
	}

	public function __destruct()
	{
		EventEmitter::removeListener('user.join', [$this, 'updateParticipatingUsers']);
		EventEmitter::removeListener('user.part', [$this, 'removeUser']);
		EventEmitter::removeListener('user.quit', [$this, 'removeUser']);
		EventEmitter::removeListener('user.mode', [$this, 'updateUserMode']);
		EventEmitter::removeListener('user.nick', [$this, 'updateUserNickname']);
		EventEmitter::removeListener('irc.line.in.353', [$this, 'updateInitialParticipatingUsers']);
	}

	/**
	 * @param string $channel
	 * @param string $mode
	 * @param User $target
	 */
	public function updateUserMode(string $channel, string $mode, User $target)
	{
		if ($channel != $this->getName())
			return;

		$shouldBeRemoved = substr($mode, 0, 1) == '-';
		$modes = substr($mode, 1);
		$modes = str_split($modes);

		foreach ($modes as $mode)
		{
			if ($shouldBeRemoved)
				$this->removeUserFromMode($mode, $target);
			else
				$this->addUserToMode($mode, $target);
		}
	}

	/**
	 * @param string $mode
	 * @param User $user
	 * @return bool
	 */
	public function isUserInMode(string $mode, User $user): bool
	{
		if (!array_key_exists($mode, $this->modeMap))
			return false;

		return in_array($user, $this->modeMap[$mode]);
	}

	/**
	 * @param string $mode
	 * @param User $user
	 * @return bool
	 */
	public function addUserToMode(string $mode, User $user): bool
	{
		if ($this->isUserInMode($mode, $user))
			return true;

		$this->modeMap[$mode][] = $user;
		return true;
	}

	/**
	 * @param string $mode
	 * @param User $user
	 * @return bool
	 */
	public function removeUserFromMode(string $mode, User $user): bool
	{
		if (!$this->isUserInMode($mode, $user))
			return false;

		$key = array_search($user, $this->modeMap[$mode]);
		unset($this->modeMap[$mode][$key]);
		return true;
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
	 * @param string $oldNickname
	 */
	public function updateUserNickname(string $oldNickname)
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
		//Logger::debug('New user structure, users now in channel', [$this->getUserCollection()->getAllUsersAsString()]);
		//Logger::debug('Added user', [$user]);
	}

	// TODO refactor this
	/**
	 * @param IncomingIrcMessage $message
	 */
	public function updateInitialParticipatingUsers(IncomingIrcMessage $message)
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
			$nickname = $user;
			$modes = $this->extractUserModesFromNickname($user, $nickname);
			$userObject = GlobalUserCollection::findOrCreateUserObject($nickname);

			if (!empty($modes))
			{
				foreach ($modes as $mode)
					$this->modeMap[$mode][] = $userObject;
			}
			$this->updateParticipatingUsers($userObject, $this->getName());
		}
	}

	/**
	 * @param string $nickname
	 * @param string $remainders
	 * @return array
	 */
	public function extractUserModesFromNickname(string $nickname, string &$remainders): array
	{
		$modeMap = ChannelDataCollector::$modeMap;
		$parts = str_split($nickname);
		$modes = [];

		foreach ($parts as $key => $part)
		{
			if (!array_key_exists($part, $modeMap))
			{
				$remainders = join('', $parts);
				break;
			}

			unset($parts[$key]);
			$modes[] = $modeMap[$part];
		}

		return $modes;
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