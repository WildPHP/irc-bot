<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Commands\Parameters;

use WildPHP\Commands\Parameters\ConvertibleParameterInterface;
use WildPHP\Commands\Parameters\Parameter;
use WildPHP\Core\Storage\IrcUserStorageInterface;

class UserParameter extends Parameter implements ConvertibleParameterInterface
{
    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * UserParameter constructor.
     * @param IrcUserStorageInterface $userStorage
     */
    public function __construct(IrcUserStorageInterface $userStorage)
    {
        parent::__construct(static function (string $value) use ($userStorage) {
            return $userStorage->getOneByNickname($value) !== null;
        });

        $this->userStorage = $userStorage;
    }

    public function convert(string $input)
    {
        $user = $this->userStorage->getOneByNickname($input);

        if ($user === null) {
            return false;
        }

        return $user;
    }
}
