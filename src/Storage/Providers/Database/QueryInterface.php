<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage\Providers\Database;


interface QueryInterface
{
    /**
     * @return string
     */
    public function toString(): string;
}