<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Connection;

class UserModeParser
{
    /**
     * @var array
     */
    protected static $prefixMap = [
        '@' => 'o',
        '%' => 'h',
        '+' => 'v'
    ];

    /**
     * @param string $nickname
     * @param string $remainders
     *
     * @return array
     */
    public static function extractFromNickname(string $nickname, string &$remainders): array
    {
        $parts = str_split($nickname);
        $modes = [];

        foreach ($parts as $key => $part) {
            if (!array_key_exists($part, self::$prefixMap)) {
                $remainders = implode('', $parts);
                break;
            }

            unset($parts[$key]);
            $modes[] = self::$prefixMap[$part];
        }

        return $modes;
    }
}
