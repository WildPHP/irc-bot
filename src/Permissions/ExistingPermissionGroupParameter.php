<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;


use WildPHP\Core\Commands\Parameter;

class ExistingPermissionGroupParameter extends Parameter
{
	public function __construct(PermissionGroupCollection $permissionGroupCollection)
	{
		parent::__construct(function (string $value) use ($permissionGroupCollection)
		{
			return $permissionGroupCollection->offsetExists($value) ? $permissionGroupCollection->offsetGet($value) : false;
		});
	}
}