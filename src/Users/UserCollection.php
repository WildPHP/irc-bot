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

use Collections\Collection;
use WildPHP\Core\ComponentContainer;

class UserCollection extends Collection
{
	/**
	 * @var ComponentContainer
	 */
	protected $container = null;
	public function __construct(ComponentContainer $container)
	{
		parent::__construct('\WildPHP\Core\Users\User');
		$this->setContainer($container);
	}

	/**
	 * @param string $nickname
	 *
	 * @return bool
	 */
	public function containsNickname(string $nickname): bool
	{
		return !empty($this->findByNickname($nickname));
	}

	/**
	 * @param string $nickname
	 * @return false|User
	 */
	public function findByNickname(string $nickname)
	{
		return $this->find(function (User $user) use ($nickname)
		{
			return $user->getNickname() == $nickname;
		});
	}

	/**
	 * @return array
	 */
	public function getAllNicknames(): array
	{
		$array = $this->toArray();

		$nicknames = [];
		foreach ($array as $user)
		{
			$nicknames[] = $user->getNickname();
		}

		return $nicknames;
	}

	/**
	 * @return false|User
	 */
	public function getSelf()
	{
		$ownNickname = $this->getContainer()->getConfiguration()->get('currentNickname')->getValue();
		return $this->findByNickname($ownNickname);
	}

	/**
	 * @param string $nickname
	 * @return User
	 */
	public function findOrCreateByNickname(string $nickname): User
	{
		if ($this->containsNickname($nickname))
			return $this->findByNickname($nickname);

		$user = new User($this->getContainer());
		$user->setNickname($nickname);
		$this->add($user);

		return $user;
	}

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}
}