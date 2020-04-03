<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Modules;

use RuntimeException;

class BrokenModule
{
    /**
     * BrokenModule constructor.
     */
    public function __construct()
    {
        throw new RuntimeException('Stuff happened and I cannot continue...');
    }
}