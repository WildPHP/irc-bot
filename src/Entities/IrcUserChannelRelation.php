<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

/**
 * Class IrcUserChannelRelation
 * @package WildPHP\Core\Entities
 *
 * @property int $ircUserId
 * @property int $ircChannelId
 */
class IrcUserChannelRelation extends Model
{
    protected $settable = [
        'ircUserId' => 'integer',
        'ircChannelId' => 'integer'
    ];

    protected $mandatory = ['ircUserId', 'ircChannelId'];
}
