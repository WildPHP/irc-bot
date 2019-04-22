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
        $lines = self::split($line);
        $index = 0;
        $amountOfPieces = count($lines);
        $object = new ParsedIrcMessage();

        if ($index < $amountOfPieces && $lines[$index][0] === '@') {
            $tags = _substr($lines[$index], 1);
            $index++;
            foreach (explode(';', $tags) as $item) {
                [$k, $v] = explode('=', $item, 2);
                if ($v === null) {
                    $object->tags[$k] = true;
                } else {
                    $object->tags[$k] = $v;
                }
            }
        }

        if ($index < $amountOfPieces && $lines[$index][0] === ':') {
            $object->prefix = _substr($lines[$index], 1);
            $index++;
        }

        if ($index < $amountOfPieces) {
            $object->verb = strtoupper($lines[$index]);
            $object->args = array_slice($lines, $index);
        }

        return $object;
    }

    /**
     * @param string $messageLine
     *
     * @return array
     */
    public static function split(string $messageLine): array
    {
        $messageLine = rtrim($messageLine, "\r\n");
        $linePieces = explode(' ', $messageLine);
        $index = 0;
        $amountOfPieces = count($linePieces);
        $usablePieces = [];

        while ($index < $amountOfPieces && $linePieces[$index] === '') {
            $index++;
        }

        if ($index < $amountOfPieces && $linePieces[$index][0] === '@') {
            $usablePieces[] = $linePieces[$index];
            $index++;
            while ($index < $amountOfPieces && $linePieces[$index] === '') {
                $index++;
            }
        }

        if ($index < $amountOfPieces && $linePieces[$index][0] === ':') {
            $usablePieces[] = $linePieces[$index];
            $index++;
            while ($index < $amountOfPieces && $linePieces[$index] === '') {
                $index++;
            }
        }

        while ($index < $amountOfPieces) {
            if ($linePieces[$index] === '') {
                $index++;
                continue;
            }

            if ($linePieces[$index][0] === ':') {
                break;
            }

            if ($linePieces[$index] !== '') {
                $usablePieces[] = $linePieces[$index];
            }
            $index++;
        }

        if ($index < $amountOfPieces) {
            $trailing = implode(' ', array_slice($linePieces, $index));
            $usablePieces[] = _substr($trailing, 1);
        }

        return $usablePieces;
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
