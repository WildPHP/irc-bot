<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;

use ValidationClosures\Types;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ComponentTrait;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\ContainerTrait;
use Yoshi2889\Collections\Collection;

class UserCollection extends Collection
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * UserCollection constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		parent::__construct(Types::instanceof(User::class));
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

	/**
	 * @return false|User
	 */
	public function getSelf()
	{
		$ownNickname = Configuration::fromContainer($this->getContainer())
			->get('currentNickname')
			->getValue();

		return $this->findOrCreateByNickname($ownNickname);
	}

	/**
	 * @param string $nickname
	 *
	 * @return User
	 */
	public function findOrCreateByNickname(string $nickname): User
	{
		if ($this->containsNickname($nickname))
			return $this->findByNickname($nickname);

		$user = new User($this->getContainer());
		$user->setNickname($nickname);
		$this->append($user);

		return $user;
	}
}