<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Authenticator;


interface RoleInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param PermissionInterface $permission
     * @return bool
     */
    public function hasPermission(PermissionInterface $permission): bool;

    /**
     * @return PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * @return array
     */
    public function toArray(): array;
}