<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


interface ParameterInterface
{
    /**
     * @param string $value
     *
     * @return false|mixed False on failure.
     */
    public function validate(string $value);
}