<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\QueueItem;
use WildPHP\Core\Connection\Commands\DummyCommand;
use WildPHP\Core\Connection\Commands\Privmsg;

class QueueTest extends PHPUnit_Framework_TestCase
{
    public function testQueueAddItem()
    {
        $queue = new \WildPHP\Core\Connection\Queue();
        $this->assertEquals(0, $queue->getAmountOfItemsInQueue());
        
        $dummyCommand = new DummyCommand();
        $queue->insertMessage($dummyCommand);
        
        $this->assertEquals(1, $queue->getAmountOfItemsInQueue());
    }

    public function testCalculateTime()
    {
        $queue = new Queue();
        $this->assertEquals(0, $queue->getAmountOfItemsInQueue());

        // If we insert 10 messages, the time the next message will be scheduled
        // should be 2*5 = 10 seconds (at a rate of 2 messages per 2 seconds)
        $expectedTime = time() + 10;

        for ($i = 1; $i <= 10; $i++)
        {
            $dummyCommand = new DummyCommand();
            $queue->insertMessage($dummyCommand);
        }

        $this->assertEquals(10, $queue->getAmountOfItemsInQueue());

        $newTime = $queue->calculateNextMessageTime();
        $this->assertEquals($expectedTime, $newTime);
    }

    public function testQueueRun()
    {
        $queue = new Queue();
        $this->assertEquals(0, $queue->getAmountOfItemsInQueue());

        for ($i = 1; $i <= 3; $i++)
        {
            $dummyCommand = new DummyCommand();
            $queue->insertMessage($dummyCommand);
        }

        sleep(2);
        $queue->flush();

        $this->assertEquals(0, $queue->getAmountOfItemsInQueue());
    }
}
