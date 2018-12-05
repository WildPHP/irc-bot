<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands\Parameters;

use WildPHP\Commands\Parameters\Parameter;
use WildPHP\Core\Entities\GroupQuery;

class GroupParameter extends Parameter
{
    /**
     * GroupParameter constructor.
     */
    public function __construct()
    {
        parent::__construct(function (string $groupName) {
            $group = GroupQuery::create()->findOneByName($groupName);

            if ($group == null)
                return false;

            return $group;
        });
    }
}