<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;

use ValidationClosures\Types;
use Yoshi2889\Collections\Collection;

class UserCollection extends Collection
{
	/**
	 * UserCollection constructor.
	 */
	public function __construct()
	{
		parent::__construct(Types::instanceof(User::class));
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
	 *
	 * @return false|User
	 */
	public function findByNickname(string $nickname)
	{
		/** @var User $value */
		foreach ($this->values() as $value)
			if ($value->getNickname() == $nickname)
				return $value;

		return false;
	}

	/**
	 * @return array
	 */
	public function getAllNicknames(): array
	{
		/** @var User[] $array */
		$array = $this->values();

		$nicknames = [];
		foreach ($array as $user)
		{
			$nicknames[] = $user->getNickname();
		}

		return $nicknames;
	}
}