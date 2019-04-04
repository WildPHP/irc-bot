<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
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
        parent::__construct(static function ($value) use ($prefix) {
            return strpos($value, $prefix) === 0;
        });
    }
}