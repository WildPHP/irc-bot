<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Authenticator;


abstract class BaseRole implements RoleInterface
{
    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var PermissionInterface[]
     */
    protected $permissions = [];

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param PermissionInterface $permission
     * @return bool
     */
    public function hasPermission(PermissionInterface $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * @return PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}