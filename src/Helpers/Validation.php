<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Helpers;

class Validation
{
    public static function array(array $array, string $type, string $keyType = null): bool
    {
        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $key => $value) {
            if ($keyType !== null && !self::is($key, $keyType)) {
                return false;
            }

            if (!self::is($value, $type)) {
                return false;
            }
        }

        return true;
    }

    public static function is($value, string $type)
    {
        if (class_exists($type)) {
            return $value instanceof $type;
        }

        return gettype($value) === $type;
    }

    /**
     * Gets the default value for a type.
     *
     * @see gettype()
     * @param string $type
     * @return mixed
     */
    public static function defaultTypeValue(string $type)
    {
        switch ($type) {
            case 'boolean':
                return false;

            case 'integer':
                return 0;

            case 'double':
                return 0.0;

            case 'string':
                return '';

            case 'array':
                return [];

            default:
                return null;
        }
    }

    /**
     * Returns the value if set, otherwise default.
     *
     * @param $value
     * @param $default
     */
    public static function default($value, $default)
    {
        return $value ?? $default;
    }
}
