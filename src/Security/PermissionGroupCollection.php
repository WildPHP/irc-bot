<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Security;

use WildPHP\Core\Collection;
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
	 * @return false|PermissionGroup
	 */
	public function findGroupByName(string $name)
	{
		/** @var PermissionGroup $value */
		foreach ($this->values() as $value)
			if ($value->getName() == $name)
				return $value;

		return false;
	}

	/**
	 * @param string $ircAccount
	 *
	 * @return Collection
	 */
	public function findAllGroupsForIrcAccount(string $ircAccount)
	{
		$groups = [];

		/** @var PermissionGroup $value */
		foreach ($this->values() as $value)
			if ($value->getUserCollection()->contains($ircAccount))
				$groups[] = $value;

		return new Collection(PermissionGroup::class, $groups);
	}
}