<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Core\Database\Database;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserNotFoundException;

class UserParameter extends Parameter
{

    /**
     * UserParameter constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        parent::__construct(function (string $value) use ($db) {
            try {
                return User::fromDatabase($db, ['nickname' => $value]);
            } catch (UserNotFoundException $exception) {
                return false;
            }
        });
    }
}