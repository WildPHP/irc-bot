<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

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
                list($k, $v) = explode('=', $item, 2);
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
     * @param string $line
     *
     * @return array
     */
    public static function split(string $line): array
    {
        $line = rtrim($line, "\r\n");
        $line = explode(' ', $line);
        $index = 0;
        $arv_count = count($line);
        $parv = [];

        while ($index < $arv_count && $line[$index] === '') {
            $index++;
        }

        if ($index < $arv_count && $line[$index][0] == '@') {
            $parv[] = $line[$index];
            $index++;
            while ($index < $arv_count && $line[$index] === '') {
                $index++;
            }
        }

        if ($index < $arv_count && $line[$index][0] == ':') {
            $parv[] = $line[$index];
            $index++;
            while ($index < $arv_count && $line[$index] === '') {
                $index++;
            }
        }

        while ($index < $arv_count) {
            if ($line[$index] === '') {
                ;
            } elseif ($line[$index][0] === ':') {
                break;
            } else {
                $parv[] = $line[$index];
            }
            $index++;
        }

        if ($index < $arv_count) {
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