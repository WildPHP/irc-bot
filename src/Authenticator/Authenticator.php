<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Authenticator;

class Authenticator
{
    /**
     * @param RoleInterface $role
     * @param PermissionInterface $permission
     * @param SubjectInterface|null $subject
     * @return bool
     */
    public static function authenticate(
        RoleInterface $role,
        PermissionInterface $permission,
        SubjectInterface $subject = null
    ): bool {
        return $role->hasPermission($permission) && ($subject != null ? $subject->hasRole($role) : true);
    }
}