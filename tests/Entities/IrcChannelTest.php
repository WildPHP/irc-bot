<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Entities;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Entities\EntityModes;
use WildPHP\Core\Entities\IrcChannel;

class IrcChannelTest extends TestCase
{

    public function testGetModesForUserId()
    {
        $channel = new IrcChannel(['name' => 'Test']);

        $original = $channel->getModesForUserId(1);
        $this->assertInstanceOf(EntityModes::class, $original);

        // get it again...
        $this->assertSame($original, $channel->getModesForUserId(1));
    }
}
