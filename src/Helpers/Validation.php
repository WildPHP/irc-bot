<?php

/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Helpers;

class Validation
{
    /**
     * Validates an array based on its values and optionally keys.
     *
     * @param array       $array   the array to validate
     * @param string      $type    the type to check for
     * @param string|null $keyType the key type to check for, or null to not check keys
     * @return bool
     * @see is()
     */
    public static function array(array $array, string $type, string $keyType = null): bool
    {
        foreach ($array as $key => $value) {
            if (!self::is($value, $type) || ($keyType !== null && !self::is($key, $keyType))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the given value is of the specified type.
     *
     * @param mixed  $value the value to check
     * @param string $type  the type of the value according to gettype(), or a class name.
     * @return bool
     * @see gettype()
     */
    public static function is($value, string $type): bool
    {
        if (class_exists($type)) {
            return $value instanceof $type;
        }

        return gettype($value) === $type;
    }

    /**
     * Gets the default value for a type.
     *
     * @param string $type
     * @return mixed
     * @see gettype()
     * @noinspection MultipleReturnStatementsInspection
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
     * @return mixed
     */
    public static function default($value, $default)
    {
        return $value ?? $default;
    }

    /**
     * Determines if the given array has the specified keys.
     *
     * @param array $array the array to check
     * @param array $mandatoryKeys the keys that the array must contain
     * @return bool
     */
    public static function arrayHasKeys(array $array, array $mandatoryKeys): bool
    {
        foreach ($mandatoryKeys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }
}
