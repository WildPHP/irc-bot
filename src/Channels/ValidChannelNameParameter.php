<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Channels;


use WildPHP\Core\Commands\Parameter;

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
            return Channel::isValidName($value, $prefix);
        });
    }
}