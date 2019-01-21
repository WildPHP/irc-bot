<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands\Parameters;

use WildPHP\Commands\Parameters\Parameter;

class ValidChannelNameParameter extends Parameter
{
    /**
     * NumericParameter constructor.
     *
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        parent::__construct(function ($value) use ($prefix) {
            return substr($value, 0, strlen($prefix)) == $prefix;
        });
    }
}