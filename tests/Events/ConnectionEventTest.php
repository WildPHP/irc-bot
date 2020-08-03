<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Events;

use WildPHP\Core\Events\ConnectionEvent;
use PHPUnit\Framework\TestCase;

class ConnectionEventTest extends TestCase
{

    public function testGetData()
    {
        $object = new ConnectionEvent('Test data');

        self::assertEquals('Test data', $object->getData());
    }

    public function testGetNullData()
    {
        $object = new ConnectionEvent();

        self::assertEquals(null, $object->getData());
    }
}
