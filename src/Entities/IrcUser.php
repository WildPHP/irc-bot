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
 * Class IrcUser
 * @package WildPHP\Core\Entities
 *
 * @property int $id
 * @property string $nickname
 * @property string $hostname
 * @property string $username
 * @property string $ircAccount
 * @property EntityModes $modes
 * @property bool $online
 */
class IrcUser extends Model
{
    protected $settable = [
        'id' => 'integer',
        'nickname' => 'string',
        'hostname' => 'string',
        'username' => 'string',
        'ircAccount' => 'string',
        'modes' => EntityModes::class,
        'online' => 'boolean'
    ];

    protected $defaults = [
        'modes' => EntityModes::class
    ];

    protected $mandatory = ['nickname'];
}
