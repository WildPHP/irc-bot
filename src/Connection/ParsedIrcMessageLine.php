<?php

/*
 * Source: https://github.com/grawity/hacks
 *
 * Licensed under the MIT Expat license:
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace WildPHP\Core\Connection;

class ParsedIrcMessageLine {
    public $tags = array();
    public $prefix = null;
    public $verb = null;
    public $args = array();

    static function split($line) {
        $line = rtrim($line, "\r\n");
        $line = explode(" ", $line);
        $i = 0; $n = count($line);
        $parv = array();

        while ($i < $n && $line[$i] === "")
            $i++;

        if ($i < $n && $line[$i][0] == "@") {
            $parv[] = $line[$i];
            $i++;
            while ($i < $n && $line[$i] === "")
                $i++;
        }

        if ($i < $n && $line[$i][0] == ":") {
            $parv[] = $line[$i];
            $i++;
            while ($i < $n && $line[$i] === "")
                $i++;
        }

        while ($i < $n) {
            if ($line[$i] === "")
                ;
            elseif ($line[$i][0] === ":")
                break;
            else
                $parv[] = $line[$i];
            $i++;
        }

        if ($i < $n) {
            $trailing = implode(" ", array_slice($line, $i));
            $parv[] = _substr($trailing, 1);
        }

        return $parv;
    }

    static function parse($line) {
        $parv = self::split($line);
        $i = 0; $n = count($parv);
        $self = new self();

        if ($i < $n && $parv[$i][0] === "@") {
            $tags = _substr($parv[$i], 1);
            $i++;
            foreach (explode(";", $tags) as $item) {
                list($k, $v) = explode("=", $item, 2);
                if ($v === null)
                    $self->tags[$k] = true;
                else
                    $self->tags[$k] = $v;
            }
        }

        if ($i < $n && $parv[$i][0] === ":") {
            $self->prefix = _substr($parv[$i], 1);
            $i++;
        }

        if ($i < $n) {
            $self->verb = strtoupper($parv[$i]);
            $self->args = array_slice($parv, $i);
        }

        return $self;
    }

    static function join($argv) {
        $i = 0; $n = count($argv);

        if ($i < $n && $argv[$i][0] == "@") {
            if (strpos($argv[$i], " ") !== false)
                return null;
            $i++;
        }

        if ($i < $n && strpos($argv[$i], " ") !== false) {
            return null;
        }

        if ($i < $n && $argv[$i][0] == ":") {
            if (strpos($argv[$i], " ") !== false)
                return null;
            $i++;
        }

        while ($i < $n-1) {
            if (!strlen($argv[$i]) || $argv[$i][0] == ":"
                || strpos($argv[$i], " ") !== false) {
                return null;
            }
            $i++;
        }

        $parv = array_slice($argv, 0, $i);

        if ($i < $n) {
            if (!strlen($argv[$i]) || $argv[$i][0] == ":"
                || strpos($argv[$i], " ") !== false) {
                $parv[] = ":".$argv[$i];
            } else {
                $parv[] = $argv[$i];
            }
        }

        return implode(" ", $parv);
    }
}

function _substr($str, $start) {
    $ret = substr($str, $start);
    return $ret === false ? "" : $ret;
}