<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */
declare(strict_types=1);

namespace WildPHP\Core\Connection\Replay;


use WildPHP\Core\Connection\Replay\Replies\ReplyInterface;

class ReplayStructure
{
    /**
     * @var ReplyInterface[]
     */
    private $replies = [];

    /**
     * Adds a reply to the reply stack.
     *
     * @param ReplyInterface $reply
     */
    public function addReply(ReplyInterface $reply): void
    {
        $this->replies[] = $reply;
    }

    /**
     * Gets the first reply that matches the given message, or null if none exists.
     *
     * @param string $msg
     * @return ReplyInterface|null
     */
    public function getReply(string $msg): ?ReplyInterface
    {
        foreach ($this->replies as $reply) {
            if ($reply->messageMatches($msg)) {
                return $reply;
            }
        }

        return null;
    }
}