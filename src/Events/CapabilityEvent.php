<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Events;


class CapabilityEvent
{
    /**
     * @var array
     */
    private $affectedCapabilities;

    public function __construct(array $affectedCapabilities)
    {
        $this->affectedCapabilities = $affectedCapabilities;
    }

    /**
     * @return array
     */
    public function getAffectedCapabilities(): array
    {
        return $this->affectedCapabilities;
    }
}