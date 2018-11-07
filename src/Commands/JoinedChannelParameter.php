<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Commands\Parameters\Parameter;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelNotFoundException;
use WildPHP\Core\Database\Database;

class JoinedChannelParameter extends Parameter
{
    /**
     * JoinedChannelParameter constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        parent::__construct(function (string $value) use ($database) {
            try {
                return Channel::fromDatabase($database, ['name' => $value]);
            } catch (ChannelNotFoundException $exception) {
                return false;
            }
        });
    }
}