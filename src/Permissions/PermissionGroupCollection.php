<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;

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
	 * @inheritdoc
	 */
	public function offsetUnset($index)
	{
		if ($this->offsetExists($index))
			$this[$index]->removeAllListeners('changed');

		$dataStorage = DataStorageFactory::getStorage('permissiongroups');
		if (in_array($index, $dataStorage->getKeys()))
			$dataStorage->delete($index);

		parent::offsetUnset($index);
	}

	/**
	 * @param string $groupName
	 *
	 * @return array
	 */
	public function getStoredGroupData(string $groupName): ?array
	{
		$dataStorage = DataStorageFactory::getStorage('permissiongroups');

		if (!in_array($groupName, $dataStorage->getKeys()))
			return null;

		return $dataStorage->get($groupName);
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