<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Security;

use ValidationClosures\Types;
use WildPHP\Core\DataStorage\DataStorageFactory;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class PermissionGroupCollection extends Collection implements ComponentInterface
{
	use ComponentTrait;

	/**
	 * PermissionGroupCollection constructor.
	 */
	public function __construct()
	{
		parent::__construct(Types::instanceof(PermissionGroup::class));
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet($offset, $value)
	{
		/** @var PermissionGroup $value */
		parent::offsetSet($offset, $value);
		$value->on('changed', function () use ($offset, $value)
		{
			$this->saveGroupData($offset, $value);
		});
	}

	/**
	 * @param string $groupName
	 * @param PermissionGroup $group
	 */
	public function saveGroupData(string $groupName, PermissionGroup $group)
	{
		$dataStorage = DataStorageFactory::getStorage('permissiongroups');
		$dataStorage->set($groupName, $group->toArray());
	}

	/**
	 * @param string $ircAccount
	 *
	 * @return Collection
	 */
	public function findAllGroupsForIrcAccount(string $ircAccount)
	{
		return $this->filter(function (PermissionGroup $group) use ($ircAccount)
		{
			return $group->getUserCollection()->contains($ircAccount);
		});
	}
}