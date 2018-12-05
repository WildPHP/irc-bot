<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands\Parameters;

use WildPHP\Commands\Parameters\Parameter;
use WildPHP\Core\Entities\IrcChannelQuery;

class ChannelParameter extends Parameter
{
    /**
     * ChannelParameter constructor.
     */
    public function __construct()
    {
        parent::__construct(function (string $value) {
            $channel = IrcChannelQuery::create()->findOneByName($value);

            if ($channel == null)
                return false;

            return $channel;
        });
    }
}