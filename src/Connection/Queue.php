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

namespace WildPHP\Core\Connection;

use WildPHP\Core\Connection\Commands\BaseCommand;
use WildPHP\Core\Connection\Commands\Privmsg;

class Queue implements QueueInterface
{
    /**
     * An explanation of how this works.
     *
     * Messages are to be 'scheduled'. That means, they will be assigned a time. This time will indicate
     * when the message is allowed to be sent.
     * This means, that when the message is set to be sent in time() + 10 seconds, the following statement is applied:
     * if (current_time() >= time() + 10) send_the_message();
     *
     * Note greater than, because the bot may have been lagging for a second which would otherwise cause the message to
     * get lost in the queue.
     */

    /**
     * @var QueueItem[]
     */
    protected $messageQueue = [];

    /**
     * @var int
     */
    protected $messageDelayInSeconds = 2;

    /**
     * @var int
     */
    protected $messagesPerSecond = 2;

    /**
     * @param BaseCommand $command
     */
    public function insertMessage(BaseCommand $command)
    {
        $time = $this->calculateNextMessageTime();
        
        $item = new QueueItem($command, $time);
        $this->scheduleItem($item);
    }

    /**
     * @param BaseCommand $command
     */
    public function removeMessage(BaseCommand $command)
    {
        if (in_array($command, $this->messageQueue))
            $this->removeMessageByIndex(array_search($command, $this->messageQueue));
    }

    /**
     * @param int $index
     */
    public function removeMessageByIndex(int $index)
    {
        if (array_key_exists($index, $this->messageQueue))
            unset($this->messageQueue[$index]);
    }

    /**
     * @param QueueItem $item
     */
    public function scheduleItem(QueueItem $item)
    {
        $this->messageQueue[] = $item;
    }

    /**
     * @return int
     */
    public function calculateNextMessageTime(): int
    {
        // If the queue is empty, this message can be sent immediately. Do not bother calculating.
        if ($this->getAmountOfItemsInQueue() == 0)
            return time();

        $numItems = $this->getAmountOfItemsInQueue();
        $messagePairs = round($numItems / $this->messagesPerSecond, 0, PHP_ROUND_HALF_DOWN);

        // For every message pair, we add the specified delay.
        $totalDelay = $messagePairs * $this->messageDelayInSeconds;

        return time() + $totalDelay;
    }

    /**
     * @return int
     */
    public function getAmountOfItemsInQueue(): int
    {
        return count($this->messageQueue);
    }

    /**
     * @return void
     */
    public function flush()
    {
        foreach ($this->messageQueue as $index => $queueItem)
        {
            if (!$queueItem->itemShouldBeTriggered())
                continue;

            // TODO
            $this->removeMessageByIndex($index);
        }
    }

    /**
     * @param string $channel
     * @param string $message
     */
    public function privmsg(string $channel, string $message)
    {
        $privmsg = new Privmsg($channel, $message);
        $this->insertMessage($privmsg);
    }
}