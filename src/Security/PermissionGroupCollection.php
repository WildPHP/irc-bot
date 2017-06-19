<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Security;


use Collections\Collection;
use WildPHP\Core\ComponentTrait;

class PermissionGroupCollection extends Collection
{
	use ComponentTrait;

	/**
	 * PermissionGroupCollection constructor.
	 */
	public function __construct()
	{
		parent::__construct(PermissionGroup::class);
	}

	/**
	 * @param string $name
	 *
	 * @return bool|mixed
	 */
	public function findGroupByName(string $name)
	{
		return $this->find(function (PermissionGroup $group) use ($name)
		{
			return $group->getName() == $name;
		});
	}

	/**
	 * @param string $ircAccount
	 *
	 * @return Collection
	 */
	public function findAllGroupsForIrcAccount(string $ircAccount)
	{
		return $this->findAll(function (PermissionGroup $group) use ($ircAccount)
		{
			return $group->isMemberByIrcAccount($ircAccount);
		});
	}
}