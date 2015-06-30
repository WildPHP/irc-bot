<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

namespace WildPHP\Connection;

use SplQueue;

use WildPHP\Manager;
use WildPHP\Bot;

class QueueManager extends Manager
{

	const VOID_QUEUE_ID = QueuePriority::VOID;
	const IMMEDIATE_QUEUE_ID = QueuePriority::IMMEDIATE;

	/**
	 * Stores the queues as an array of pointers to first and last elements of the queue.
	 * @var array<QueuePriority, array<'first'<QueueItem>,'last'<QueueItem>>>
	 */
	protected $queues;

	/**
	 * The flood limit for max. lines per second that will be returned from the queues.
	 * @var int|double
	 */
	protected $linesPerSecond;

	/**
	 * The burst of lines that will be returned from the queues if nothing was returned previously
	 * @var int
	 */
	protected $linesMaxBurst;

	/**
	 * Current burst of lines available to be returned from the queues.
	 * @var int
	 */
	protected $burst;

	/**
	 * Stores when was the last flood check processed.
	 * Used to refill $burst from the limits.
	 * @var float
	 */
	protected $lastFloodCheck;

	/**
	 * Stores the total amount of lines in all queues.
	 * @var int
	 */
	protected $linesAvailable = 0;
	
	/**
	 * The message count.
	 * @var int
	 */
	protected $messageCount = 0;

	/**
	 * Initializes the queues. You can specify custom flood limits, but the defaults should be safe for most servers.
	 * @see setFloodLimits()
	 */
	public function __construct(Bot $bot, $linesPerSecond = 2, $linesMaxBurst = 4)
	{
		parent::__construct($bot);
		$this->setFloodLimits($linesPerSecond, $linesMaxBurst);

		// Prepares the queues array so that we can later easily iterate over them
		$this->recreateQueues();
	}

	/**
	 * Sets new flood limits.
	 *
	 * @param double $linesPerSecond Maximum lines per second that will be sent to output. Set to zero for no limit. Must be >= 0.
	 * @param int $linesMaxBurst Maximum lines that will be sent as an initial burst. Defaults to 1. Must be >= 1.
	 * @throws InvalidArgumentException
	 */
	public function setFloodLimits($linesPerSecond, $linesMaxBurst = 1)
	{
		if(!is_int($linesPerSecond) && !is_float($linesPerSecond))
			throw new InvalidArgumentException('Argument $linesPerSecond is invalid: expeted double, got ' . gettype($linesPerSecond) . '.');

		if(!is_int($linesMaxBurst))
			throw new InvalidArgumentException('Argument $linesMaxBurst is invalid: expeted integer, got ' . gettype($linesPerSecond) . '.');

		if($linesPerSecond < 0)
			throw new InvalidArgumentException('Argument $linesPerSecond is invalid: must be greater than or equal to zero.');
		
		if($linesMaxBurst < 1)
			throw new InvalidArgumentException('Argument $linesMaxBurst is invalid: must be greater than or equal to zero.');

		$this->linesPerSecond = $linesPerSecond;
		$this->linesMaxBurst = $linesMaxBurst;
	}

	/**
	 * Does a flood check, setting the current available burst to whatever the limits allow.
	 */
	protected function floodCheck()
	{
		// Seconds since the last check
		$timeDiff = microtime(true) - $this->lastFloodCheck;
		$this->lastFloodCheck = microtime(true);

		// Lines that were replenished in that time
		$linesAvail = (int) ceil($timeDiff * $this->linesPerSecond);

		if($linesAvail > $this->linesMaxBurst)
			$this->burst = $this->linesMaxBurst;
		else
			$this->burst = $linesAvail;
	}

	/**
	 * Queues a new message at given priority.
	 * @param string $message The message.
	 * @param null|QueuePriority $priority The desired priority. Defaults to NORMAL.
	 * @return void
	 * @throws InvalidArgumentException when $message is not a string.
	 */
	public function enqueue($message, QueuePriority $priority = null)
	{
		if($priority === null)
			$priority = QueuePriority::NORMAL;
		else
			$priority = $priority->getValue();

		if(!is_string($message))
			throw new InvalidArgumentException('Parameter $message is invalid: expected string, got ' . gettype($data) . '.');

		// Message goes to void - discarding
		if($priority === QueuePriority::VOID)
			return;

		// We willl be adding a retreivable message; increase message count
		$this->messageCount++;

		/*
			Message goes to the immediate queue.
			It is implemented as an array since it's faster,
			but this is a somewhat ugly way to do that.
		*/
		if($priority === QueuePriority::IMMEDIATE)
		{
			$this->queues[QueuePriority::IMMEDIATE][] = $message;
			return;
		}

		// Everything else goes here
		$this->queues[$priority]->enqueue($message);
	}

	/**
	 * Returns an array of queued messages respecting the limits and removing the messages from their queues.
	 * @return string[] Array of messages from the queues.
	 */
	public function getQueuedItems()
	{
		// when there are no messages we don't need to do much
		if(!$this->linesAvailable)
			return array();

		$this->floodCheck();

		// Handle the immediate queue
		$messages = $this->getImmediateQueueContents();

		// Handle all the other queues
		foreach($this->queues as $priority => $queue)
		{
			// Immediate queue has already been processed
			if($priority === QueuePriority::IMMEDIATE)
				continue;

			// Get messages until limits stop us or the queue is empty
			while(!$queue->isEmpty() && ($this->burst > 0) && ($this->messageCount > 0))
			{
				$messages[] = $this->queues[QueuePriority::IMMEDIATE]->dequeue();
				$this->messageCount--;
				$this->burst--;
			}

			// we can't take more / there are no more messages, so we just stop
			if($this->burst <= 0 || $this->messageCount <= 0)
				break;
		}

		return $messages;
	}

	/**
	 * Returns contents of the immediate "queue" array.
	 * The messages count toward flood limits and the array is reset.
	 * @return string[] messages from the immediate queue
	 */
	protected function getImmediateQueueContents()
	{
		// Alias the queue for readability
		$queue = &$this->queues[QueuePriority::IMMEDIATE];

		// Return empty-handed if we are ... empty.
		if(empty($queue))
			return array();

		// Grab a copy and clear the queue
		$messages = $queue;
		$queue = array();

		// Reduce the burst and amount of "lines available"
		$messageCount = count($messages);
		$this->burst = $this->burst - $messageCount < 0 ? 0 : $this->burst - $messageCount;

		// Substract the amount from the global "lines available" count too
		$this->linesAvailable -= $messageCount;

		// Finally return the messages
		return $messages;
	}

	/**
	 * Returns the amount of messages that are available for retreival
	 * @return int count of available messages
	 */
	public function getMessageCount()
	{
		return $this->linesAvailable;
	}

	/**
	 * Recreates all queues effectively purging them.
	 * The queues are sorted by priority.
	 */
	public function recreateQueues()
	{

		foreach (QueuePriority::toArray() as $priority)
		{
			// Neither the IMMEDIATE or the VOID queue are real queues, so we don't create them here
			if($priority === QueuePriority::IMMEDIATE || $priority === QueuePriority::VOID)
				continue;

			$this->queues[$priority] = new SplQueue();
		}

		$this->queues[QueuePriority::IMMEDIATE] = array();

		ksort($this->queues, SORT_NUMERIC);
	}

}
