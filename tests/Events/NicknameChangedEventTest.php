<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Events;

use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Events\NicknameChangedEvent;
use PHPUnit\Framework\TestCase;

class NicknameChangedEventTest extends TestCase
{

    public function testGetProperties()
    {
        $user = new IrcUser(['nickname' => 'TestUser']);
        $old = 'oldNickname';
        $new = 'newNickname';
        $object = new NicknameChangedEvent($user, $old, $new);

        self::assertEquals($old, $object->getOldNickname());
        self::assertEquals($new, $object->getNewNickname());
        self::assertSame($user, $object->getUser());
    }
}
