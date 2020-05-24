<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Events;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Events\EventEmitter;

class EventEmitterTest extends TestCase
{
    public function testFirst()
    {
        $eventEmitter = new EventEmitter();

        $eventEmitter->on('test', [$this, 'ing']);
        $eventEmitter->on('test', [$this, 'foo']);
        $eventEmitter->first('test', [$this, 'bar']);

        $listeners = $eventEmitter->listeners('test');
        self::assertEquals([[$this, 'bar'], [$this, 'ing'], [$this, 'foo']], $listeners);
        self::assertEquals([$this, 'bar'], array_shift($listeners));

        $eventEmitter->on('ing', [$this, 'foo']);
        $listeners = $eventEmitter->listeners('ing');
        self::assertEquals([[$this, 'foo']], $listeners);
        self::assertEquals([$this, 'foo'], array_shift($listeners));

        $eventEmitter->first('tester', [$this, 'ing']);
        self::assertEquals([[$this, 'ing']], $eventEmitter->listeners('tester'));
    }

    public function ing()
    {
    }

    public function foo()
    {
    }

    public function bar()
    {
    }
}
