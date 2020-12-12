<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Events;

use WildPHP\Core\Events\CapabilityEvent;
use PHPUnit\Framework\TestCase;

class CapabilityEventTest extends TestCase
{

    public function testGetAffectedCapabilities()
    {
        $caps = ['cap1', 'cap2'];

        $event = new CapabilityEvent($caps);

        self::assertEquals($caps, $event->getAffectedCapabilities());
    }
}
