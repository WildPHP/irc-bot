<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

class IrcUserChannelRelation
{
    /**
     * @var int
     */
    private $ircUserId;

    /**
     * @var int
     */
    private $ircChannelId;

    /**
     * @return int
     */
    public function getIrcUserId(): int
    {
        return $this->ircUserId;
    }

    /**
     * @param int $ircUserId
     */
    public function setIrcUserId(int $ircUserId): void
    {
        $this->ircUserId = $ircUserId;
    }

    /**
     * @return int
     */
    public function getIrcChannelId(): int
    {
        return $this->ircChannelId;
    }

    /**
     * @param int $ircChannelId
     */
    public function setIrcChannelId(int $ircChannelId): void
    {
        $this->ircChannelId = $ircChannelId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->getIrcUserId(),
            'channel_id' => $this->getIrcChannelId()
        ];
    }

    /**
     * @param array $previousState
     * @return IrcUserChannelRelation
     */
    public static function fromArray(array $previousState): IrcUserChannelRelation
    {
        $relation = new self();
        $relation->setIrcChannelId($previousState['channel_id'] ?? 0);
        $relation->setIrcUserId($previousState['user_id'] ?? 0);
        return $relation;
    }
}
