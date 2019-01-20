<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Authenticator;


abstract class BasePermission implements PermissionInterface
{
    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var string
     */
    protected $friendlyString = '';

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getFriendlyString(): string
    {
        return $this->friendlyString;
    }
}