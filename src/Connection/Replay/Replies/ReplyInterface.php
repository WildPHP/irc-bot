<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Replay\Replies;


interface ReplyInterface
{
    /**
     * Checks whether a message matches this reply.
     *
     * @param string $msg the message to match
     * @return bool whether the message matches or not
     */
    public function messageMatches(string $msg): bool;

    /**
     * Process the passed message.
     *
     * @param string $msg
     * @return void
     */
    public function process(string $msg): void;
}