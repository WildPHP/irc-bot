<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use WildPHP\Core\EventEmitter;

class EventEmitterTest extends \PHPUnit\Framework\TestCase
{
	public function testFirst()
	{
		$eventEmitter = new EventEmitter();

		$eventEmitter->on('test', [$this, 'ing']);
		$eventEmitter->on('test', [$this, 'foo']);
		$eventEmitter->first('test', [$this, 'bar']);

		self::assertEquals([[$this, 'bar'], [$this, 'ing'], [$this, 'foo']], $eventEmitter->listeners('test'));
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
