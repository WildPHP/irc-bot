<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Authenticator;


abstract class BaseSubject implements SubjectInterface
{
    protected $identifier = '';

    /**
     * @var RoleInterface[]
     */
    protected $roles = [];

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function hasRole(RoleInterface $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * @return RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}