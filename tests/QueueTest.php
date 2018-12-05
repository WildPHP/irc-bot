<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\QueueItem;
use WildPHP\Messages\Privmsg;
use WildPHP\Messages\Raw;

class QueueTest extends TestCase
{
	protected $container;

	public function testQueueAddItem()
    {
        $eventEmitter = new \WildPHP\Core\EventEmitter();
        $queue = new Queue($eventEmitter);
        static::assertEquals(0, $queue->count());
        
        $dummyCommand = new Raw('test');
        $queue->insertMessage($dummyCommand);
        
        static::assertEquals(1, $queue->count());
    }

	public function testQueueRemoveItem()
	{
	    $eventEmitter = new \WildPHP\Core\EventEmitter();
		$queue = new Queue($eventEmitter);
		static::assertEquals(0, $queue->count());

		$dummyCommand = new Raw('test');
		$queueItem = $queue->insertMessage($dummyCommand);

		static::assertEquals(1, $queue->count());
		
		static::assertTrue($queue->removeMessage($queueItem));

		static::assertEquals(0, $queue->count());
		static::assertFalse($queue->removeMessage($queueItem));
    }

	public function testQueueRemoveItemByIndex()
	{
	    $eventEmitter = new \WildPHP\Core\EventEmitter();
		$queue = new Queue($eventEmitter);
		static::assertEquals(0, $queue->count());

		$dummyCommand = new Raw('test');
		$queue->insertMessage($dummyCommand);

		static::assertEquals(1, $queue->count());

		static::assertTrue($queue->removeMessageByIndex(0));

		static::assertEquals(0, $queue->count());
		static::assertFalse($queue->removeMessageByIndex(0));
    }

	public function testQueueClear()
	{
	    $eventEmitter = new \WildPHP\Core\EventEmitter();
		$queue = new Queue($eventEmitter);
		static::assertEquals(0, $queue->count());

		$dummyCommand = new Raw('test');
		$queue->insertMessage($dummyCommand);

		static::assertEquals(1, $queue->count());

		$queue->clear();

		static::assertEquals(0, $queue->count());
    }

    public function testCalculateTimeWithoutFloodControl()
    {
        $eventEmitter = new \WildPHP\Core\EventEmitter();
        $queue = new Queue($eventEmitter);
        $queue->setFloodControl(false);
        static::assertEquals(0, $queue->count());

        // No matter how many messages we insert, with flood control disabled we should have no delays between messages.
        // Thus, total time should be equal to our current time.
        $expectedTime = time();

        for ($i = 1; $i <= 10; $i++)
        {
            $dummyCommand = new Raw('test');
            $queue->insertMessage($dummyCommand);
        }

        static::assertEquals(10, $queue->count());

        $newTime = $queue->calculateNextMessageTimestamp();
        static::assertEquals($expectedTime, $newTime);
    }

    public function testCalculateTime()
    {
        $eventEmitter = new \WildPHP\Core\EventEmitter();
        $queue = new Queue($eventEmitter);
        $queue->setFloodControl(true);
        static::assertEquals(0, $queue->count());

        // If we insert 10 messages, the time the next message will be scheduled
        // should be 1*10 = 10 seconds (at a rate of 1 message per second)
	    // However, the queue system allows bursting. So the first 5 messages get no timeout.
	    // Therefore the calculation is 5*0 + 5*1 = 5 seconds for the next message.
        $expectedTime = time() + 5;

        for ($i = 1; $i <= 10; $i++)
        {
            $dummyCommand = new Raw('test');
            $queue->insertMessage($dummyCommand);
        }

        static::assertEquals(10, $queue->count());

        $newTime = $queue->calculateNextMessageTimestamp();
        static::assertEquals($expectedTime, $newTime);
    }

    public function testQueueRun()
    {
        $eventEmitter = new \WildPHP\Core\EventEmitter();
        $queue = new Queue($eventEmitter);
        static::assertEquals(0, $queue->count());

        for ($i = 1; $i <= 3; $i++)
        {
            $dummyCommand = new Privmsg('#test', 'test');
            $queue->insertMessage($dummyCommand);
        }
        $dueItems = $queue->getDueItems();
        $queue->processQueueItems($dueItems);

        static::assertEquals(0, $queue->count());

        
	    $queue->setFloodControl();
	    for ($i = 1; $i <= 50; $i++)
	    {
		    $dummyCommand = new Privmsg('#test', 'test');
		    $queue->insertMessage($dummyCommand);
	    }

	    static::assertEquals(50, $queue->count());
        $dueItems = $queue->getDueItems();
        $queue->processQueueItems($dueItems);
	    
	    static::assertEquals(44, $queue->count());
    }

	public function testInitializeMessage()
	{
	    $eventEmitter = new \WildPHP\Core\EventEmitter();
		$queue = new Queue($eventEmitter);
		
		$queueItem = $queue->raw('Test');
		self::assertEquals(1, $queue->count());
		
		$expectedQueueItem = new QueueItem(new Raw('Test'), time());
		self::assertEquals($expectedQueueItem, $queueItem);
    }
}
