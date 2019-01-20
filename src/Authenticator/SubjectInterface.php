<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Authenticator;


interface SubjectInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param RoleInterface $role
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool;

    /**
     * @return array
     */
    public function getRoles(): array;

    /**
     * @return array
     */
    public function toArray(): array;
}