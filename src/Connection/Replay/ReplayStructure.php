<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Replay;


class ReplayStructure
{
    /**
     * @var callable[]
     */
    private $replies = [];

    public function reply(string $msg, callable $callback)
    {
        $this->replies[$msg] = $callback;
    }

    public function getReply(string $msg): ?callable
    {
        return $this->replies[$msg] ?? null;
    }
}