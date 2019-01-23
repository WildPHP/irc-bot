<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands\Parameters;

use WildPHP\Commands\Parameters\Parameter;
use WildPHP\Core\Storage\IrcChannelStorageInterface;

class ChannelParameter extends Parameter
{
    /**
     * ChannelParameter constructor.
     * @param IrcChannelStorageInterface $channelStorage
     */
    public function __construct(IrcChannelStorageInterface $channelStorage)
    {
        parent::__construct(function (string $value) use ($channelStorage) {
            $channel = $channelStorage->getOneByName($value);

            if ($channel === null) {
                return false;
            }

            return $channel;
        });
    }
}