<?php

/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */
declare(strict_types=1);

namespace WildPHP\Core\Connection\Replay\Replies;

use WildPHP\Core\Connection\MessageParser;

class ExactReply extends BaseReply
{
    /**
     * @var string
     */
    private $match;

    /**
     * ExactReply constructor.
     * @param string $match
     * @param callable $callback
     */
    public function __construct(string $match, callable $callback)
    {
        $this->match = $match;
        $this->callback = $callback;
    }

    public function messageMatches(string $msg): bool
    {
        return $msg === $this->match;
    }
}