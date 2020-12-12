<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Events;

use WildPHP\Core\Events\IncomingIrcMessageEvent;
use PHPUnit\Framework\TestCase;
use WildPHP\Core\Events\OutgoingIrcMessageEvent;
use WildPHP\Core\Events\UnsupportedIncomingIrcMessageEvent;
use WildPHP\Messages\Generics\IrcMessage;
use WildPHP\Messages\Privmsg;
use WildPHP\Messages\RPL\Welcome;

class IrcMessageEventTest extends TestCase
{

    public function testGetIncomingMessage()
    {
        $message = new Welcome();
        $object = new IncomingIrcMessageEvent($message);

        self::assertSame($message, $object->getIncomingMessage());
    }

    public function testGetOutgoingMessage()
    {
        $message = new Privmsg('#testChannel', 'Hello!');
        $object = new OutgoingIrcMessageEvent($message);

        self::assertSame($message, $object->getOutgoingMessage());
    }

    public function testGetUnsupportedIncomingMessage()
    {
        $message = new IrcMessage('test', 'test');
        $object = new UnsupportedIncomingIrcMessageEvent($message);

        self::assertSame($message, $object->getMessage());
    }
}
