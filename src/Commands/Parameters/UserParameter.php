<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands\Parameters;

use WildPHP\Commands\Parameters\Parameter;
use WildPHP\Core\Entities\IrcUserQuery;

class UserParameter extends Parameter
{
    /**
     * UserParameter constructor.
     */
    public function __construct()
    {
        parent::__construct(function (string $value) {
            $user = IrcUserQuery::create()->findOneByNickname($value);

            if ($user == null)
                return false;

            return $user;
        });
    }
}