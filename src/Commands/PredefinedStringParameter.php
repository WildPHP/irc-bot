<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

class PredefinedStringParameter extends Parameter
{
    /**
     * PredefinedStringParameter constructor.
     *
     * @param string $expected
     */
    public function __construct(string $expected)
    {
        parent::__construct(function (string $value) use ($expected) {
            return $value == $expected;
        });
    }
}