<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Connection;

class MessageParser
{
    /**
     * @param string $line
     *
     * @return ParsedIrcMessage
     */
    public static function parseLine(string $line): ParsedIrcMessage
    {
        $parv = self::split($line);
        $index = 0;
        $parv_count = count($parv);
        $self = new ParsedIrcMessage();

        if ($index < $parv_count && $parv[$index][0] === '@') {
            $tags = _substr($parv[$index], 1);
            $index++;
            foreach (explode(';', $tags) as $item) {
                [$k, $v] = explode('=', $item, 2);
                if ($v === null) {
                    $self->tags[$k] = true;
                } else {
                    $self->tags[$k] = $v;
                }
            }
        }

        if ($index < $parv_count && $parv[$index][0] === ':') {
            $self->prefix = _substr($parv[$index], 1);
            $index++;
        }

        if ($index < $parv_count) {
            $self->verb = strtoupper($parv[$index]);
            $self->args = array_slice($parv, $index);
        }

        return $self;
    }

    /**
     * @param string $messageLine
     *
     * @return array
     */
    public static function split(string $messageLine): array
    {
        $messageLine = rtrim($messageLine, "\r\n");
        $line = explode(' ', $messageLine);
        $index = 0;
        $arvCount = count($line);
        $parv = [];

        while ($index < $arvCount && $line[$index] === '') {
            $index++;
        }

        if ($index < $arvCount && $line[$index][0] === '@') {
            $parv[] = $line[$index];
            $index++;
            while ($index < $arvCount && $line[$index] === '') {
                $index++;
            }
        }

        if ($index < $arvCount && $line[$index][0] === ':') {
            $parv[] = $line[$index];
            $index++;
            while ($index < $arvCount && $line[$index] === '') {
                $index++;
            }
        }

        while ($index < $arvCount) {
            if ($line[$index][0] === ':') {
                break;
            }

            if ($line[$index] !== '') {
                $parv[] = $line[$index];
            }
            $index++;
        }

        if ($index < $arvCount) {
            $trailing = implode(' ', array_slice($line, $index));
            $parv[] = _substr($trailing, 1);
        }

        return $parv;
    }
}

/**
 * @param $str
 * @param $start
 *
 * @return bool|string
 */
function _substr($str, $start)
{
    $ret = substr($str, $start);

    return $ret === false ? '' : $ret;
}
