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

class QueueTest extends TestCase
{
	protected $container;

	public function setUp()
	{
		$this->container = new \WildPHP\Core\ComponentContainer();
		$klogger = new Katzgrau\KLogger\Logger('php://stdout');
		$this->container->add(new \WildPHP\Core\Logger\Logger($klogger));
	}

	public function testQueueAddItem()
    {
        $queue = new \WildPHP\Core\Connection\Queue($this->container);
        static::assertEquals(0, $queue->getAmountOfItemsInQueue());
        
        $dummyCommand = new \WildPHP\Core\Connection\IRCMessages\RAW('test');
        $queue->insertMessage($dummyCommand);
        
        static::assertEquals(1, $queue->getAmountOfItemsInQueue());
    }

    public function testCalculateTimeWithoutFoodControl()
    {
        $queue = new Queue($this->container);
        $queue->setFloodControl(false);
        static::assertEquals(0, $queue->getAmountOfItemsInQueue());

        // No matter how many messages we insert, with flood control disabled we should have no delays between messages.
        // Thus, total time should be equal to our current time.
        $expectedTime = time();

        for ($i = 1; $i <= 10; $i++)
        {
            $dummyCommand = new \WildPHP\Core\Connection\IRCMessages\RAW('test');
            $queue->insertMessage($dummyCommand);
        }

        static::assertEquals(10, $queue->getAmountOfItemsInQueue());

        $newTime = $queue->calculateNextMessageTime();
        static::assertEquals($expectedTime, $newTime);
    }

    public function testCalculateTime()
    {
        $queue = new Queue($this->container);
        $queue->setFloodControl(true);
        static::assertEquals(0, $queue->getAmountOfItemsInQueue());

        // If we insert 10 messages, the time the next message will be scheduled
        // should be 1*10 = 10 seconds (at a rate of 1 message per second)
	    // However, the queue system allows bursting. So the first 5 messages get no timeout.
	    // Therefore the calculation is 5*0 + 5*1 = 5 seconds for the next message.
        $expectedTime = time() + 5;

        for ($i = 1; $i <= 10; $i++)
        {
            $dummyCommand = new \WildPHP\Core\Connection\IRCMessages\RAW('test');
            $queue->insertMessage($dummyCommand);
        }

        static::assertEquals(10, $queue->getAmountOfItemsInQueue());

        $newTime = $queue->calculateNextMessageTime();
        static::assertEquals($expectedTime, $newTime);
    }

    public function testQueueRun()
    {
        $queue = new Queue($this->container);
        static::assertEquals(0, $queue->getAmountOfItemsInQueue());

        for ($i = 1; $i <= 3; $i++)
        {
            $dummyCommand = new \WildPHP\Core\Connection\IRCMessages\RAW('test');
            $queue->insertMessage($dummyCommand);
        }

        sleep(2);
        $queue->flush();

        static::assertEquals(0, $queue->getAmountOfItemsInQueue());
    }
}
