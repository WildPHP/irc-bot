<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;

interface CapabilityInterface
{
    /**
     * @return bool
     */
    public function finished(): bool;

    /**
     * @return array
     */
    public function getCapabilities(): array;
}