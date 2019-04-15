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
use WildPHP\Core\Storage\IrcUserStorageInterface;

class UserParameter extends Parameter
{
    /**
     * UserParameter constructor.
     * @param IrcUserStorageInterface $userStorage
     */
    public function __construct(IrcUserStorageInterface $userStorage)
    {
        parent::__construct(static function (string $value) use ($userStorage) {
            $user = $userStorage->getOneByNickname($value);

            if ($user === null) {
                return false;
            }

            return $user;
        });
    }
}
