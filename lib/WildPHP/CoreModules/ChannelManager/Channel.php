<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

namespace WildPHP\CoreModules\ChannelManager;

class Channel
{
	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var string[]
	 */
	protected $users;

	/**
	 * @param string $name
	 * @param string[] $users
	 */
	public function __construct($name, $users)
	{
		$this->setName($name);
		$this->setUsers($users);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param string[] $users
	 */
	public function setUsers($users)
	{
		$this->users = $users;
	}

	/**
	 * @param string $user
	 */
	public function addUser($user)
	{
		if ($this->userExists($user))
			return;

		$this->users[] = $user;
	}

	/**
	 * @param string $user
	 *
	 * @return boolean
	 */
	public function userExists($user)
	{
		return in_array($user, $this->users);
	}

	/**
	 * @param string $user
	 */
	public function removeUser($user)
	{
		if (!$this->userExists($user))
			return;

		$key = array_search($user, $this->users);
		unset($this->users[$key]);
	}
}