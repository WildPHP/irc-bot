<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Replay\Replies;


use WildPHP\Core\Connection\MessageParser;

abstract class BaseReply implements ReplyInterface
{
    /**
     * @var callable
     */
    protected $callback;

    public function process(string $msg): void
    {
        $parsed = MessageParser::parseLine($msg);
        call_user_func($this->callback, $parsed);
    }
}