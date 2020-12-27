<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Replay\Replies;

class RegexReply extends BaseReply
{
    /**
     * @var string
     */
    private $regex;

    /**
     * RegexReply constructor.
     * @param string $regex
     * @param callable $callback
     */
    public function __construct(/** @lang PhpRegExp */ string $regex, callable $callback)
    {
        $this->regex = $regex;
        $this->callback = $callback;
    }

    public function messageMatches(string $msg): bool
    {
        return preg_match($this->regex, $msg) === 1;
    }
}