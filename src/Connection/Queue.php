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
use WildPHP\Core\Connection\Commands\Cap;
use WildPHP\Core\Connection\Commands\Join;
use WildPHP\Core\Connection\Commands\Nick;
use WildPHP\Core\Connection\Commands\Part;
use WildPHP\Core\Connection\Commands\Pong;
use WildPHP\Core\Connection\Commands\Privmsg;
use WildPHP\Core\Connection\Commands\Quit;
use WildPHP\Core\Connection\Commands\User;
use WildPHP\Core\Connection\Commands\Who;

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
	protected $messageDelayInSeconds = 1;

	/**
	 * @var int
	 */
	protected $messagesPerSecond = 1;

	/**
	 * @var bool
	 */
	protected $floodControlEnabled = false;

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
		if ($this->getAmountOfItemsInQueue() == 0 || !$this->isFloodControlEnabled())
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
	 * @return QueueItem[]
	 */
	public function flush(): array
	{
		$expired = [];
		foreach ($this->messageQueue as $index => $queueItem)
		{
			if (!$queueItem->itemShouldBeTriggered())
				continue;

			$expired[] = $queueItem;
			$this->removeMessageByIndex($index);
		}

		return $expired;
	}

	/**
	 * @param bool $enabled
	 */
	public function setFloodControl(bool $enabled = true)
	{
		$this->floodControlEnabled = $enabled;
	}

	/**
	 * @return bool
	 */
	public function isFloodControlEnabled(): bool
	{
		return $this->floodControlEnabled;
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

	/**
	 * @param string $nickname
	 */
	public function nick(string $nickname)
	{
		$nick = new Nick($nickname);
		$this->insertMessage($nick);
	}

	/**
	 * @param string $username
	 * @param string $hostname
	 * @param string $servername
	 * @param string $realname
	 */
	public function user(string $username, string $hostname, string $servername, string $realname)
	{
		$user = new User($username, $hostname, $servername, $realname);
		$this->insertMessage($user);
	}

	/**
	 * @param string $server
	 */
	public function pong(string $server)
	{
		$pong = new Pong($server);
		$this->insertMessage($pong);
	}

	/**
	 * @param array|string $channel
	 * @param string $key
	 */
	public function join($channel, $key = '')
	{
		$join = new Join($channel, $key);
		$this->insertMessage($join);
	}

	/**
	 * @param string $channel
	 */
	public function part(string $channel)
	{
		$part = new Part($channel);
		$this->insertMessage($part);
	}

	/**
	 * @param string $command
	 */
	public function cap(string $command)
	{
		$cap = new Cap($command);
		$this->insertMessage($cap);
	}

	/**
	 * @param string $channel
	 * @param string $options
	 */
	public function who(string $channel, string $options = '')
	{
		$who = new Who($channel, $options);
		$this->insertMessage($who);
	}

	/**
	 * @param string $message
	 */
	public function quit(string $message)
	{
		$quit = new Quit($message);
		$this->insertMessage($quit);
	}
}