<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

/**
 * Class IrcChannel
 * @package WildPHP\Core\Entities
 *
 * @property int $channelId
 * @property string $name
 * @property string $topic
 * @property EntityModes $modes
 * @property EntityModes[] $userModes
 */
class IrcChannel extends Model
{
    protected $settable = [
        'channelId' => 'integer',
        'name' => 'string',
        'topic' => 'string',
        'modes' => EntityModes::class,
        'userModes' => ['array', EntityModes::class, 'integer']
    ];

    protected $defaults = [
        'modes' => EntityModes::class
    ];

    protected $mandatory = ['name'];

    /**
     * @param int $userId
     * @return mixed|EntityModes
     */
    public function getModesForUserId(int $userId)
    {
        if (!array_key_exists($userId, $this->userModes)) {
            $this->userModes[$userId] = new EntityModes();
        }

        return $this->userModes[$userId];
    }
}
