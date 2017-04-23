<?php

namespace WildPHP\Core\Security;


use WildPHP\Core\DataStorage\DataStorage;

class GlobalPermissionGroupCollection
{
	/**
	 * @var PermissionGroupCollection
	 */
	protected static $permissionGroupCollection;

	/**
	 * @return PermissionGroupCollection
	 */
	public static function getPermissionGroupCollection(): PermissionGroupCollection
	{
		return self::$permissionGroupCollection;
	}

	/**
	 * @param PermissionGroupCollection $permissionGroupCollection
	 */
	public static function setPermissionGroupCollection(PermissionGroupCollection $permissionGroupCollection)
	{
		self::$permissionGroupCollection = $permissionGroupCollection;
	}

	public static function setup()
	{
		GlobalPermissionGroupCollection::setPermissionGroupCollection(new PermissionGroupCollection('\WildPHP\Core\Security\PermissionGroup'));
		$dataStorage = new DataStorage('permissiongrouplist');

		$groupsToLoad = $dataStorage->get('groupstoload');
		foreach ($groupsToLoad as $group)
		{
			$pGroup = new PermissionGroup($group, true);
			self::getPermissionGroupCollection()->add($pGroup);
		}

		register_shutdown_function(function ()
		{
			$groups = self::getPermissionGroupCollection()->toArray();
			$groupList = [];

			foreach ($groups as $group)
				$groupList[] = $group->getName();

			$dataStorage = new DataStorage('permissiongrouplist');
			$dataStorage->set('groupstoload', $groupList);
		});
	}
}