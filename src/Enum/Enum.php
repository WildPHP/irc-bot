<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Enum;

use ReflectionClass;
use ReflectionException;

/**
 * Class Enum
 * @package WildPHP\Core\Enum
 *
 * Based on code found in: https://stackoverflow.com/questions/254514/php-and-enumerations/254543#254543
 */
abstract class Enum
{
    /**
     * @var null|array
     */
    private static $cacheArray;

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function toArray(): array
    {
        if (self::$cacheArray === null) {
            self::$cacheArray = [];
        }

        $calledClass = static::class;

        if (!array_key_exists($calledClass, self::$cacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$cacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$cacheArray[$calledClass];
    }
}
