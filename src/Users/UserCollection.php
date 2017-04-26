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
use WildPHP\Core\Configuration\Configuration;

class UserCollection extends Collection
{
	protected static $globalInstance = null;

	/**
	 * @return UserCollection
	 */
	public static function getGlobalInstance(): UserCollection
	{
		if (is_null(self::$globalInstance))
			self::$globalInstance = new UserCollection();

		return self::$globalInstance;
	}

	public function __construct()
	{
		parent::__construct('\WildPHP\Core\Users\User');
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
		$ownNickname = Configuration::get('currentNickname')->getValue();
		return $this->findByNickname($ownNickname);
	}

	/**
	 * @return false|User
	 */
	public static function getGlobalSelf()
	{
		$ownNickname = Configuration::get('currentNickname')->getValue();
		$collection = self::getGlobalInstance();
		return $collection->findByNickname($ownNickname);
	}

	/**
	 * @param string $nickname
	 * @param bool $addToCollection
	 * @return User
	 */
	public static function globalFindOrCreateByNickname(string $nickname, bool $addToCollection = true): User
	{
		$collection = self::getGlobalInstance();
		if ($collection->containsNickname($nickname))
			return $collection->findByNickname($nickname);

		$user = new User();
		$user->setNickname($nickname);

		if ($addToCollection)
			$collection->add($user);

		return $user;
	}
}