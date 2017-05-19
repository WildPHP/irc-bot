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

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ComponentTrait;
use WildPHP\Core\Connection\IRCMessages\BaseMessage;
use WildPHP\Core\Connection\IRCMessages\CAP;
use WildPHP\Core\Connection\IRCMessages\JOIN;
use WildPHP\Core\Connection\IRCMessages\KICK;
use WildPHP\Core\Connection\IRCMessages\MODE;
use WildPHP\Core\Connection\IRCMessages\NICK;
use WildPHP\Core\Connection\IRCMessages\PART;
use WildPHP\Core\Connection\IRCMessages\PING;
use WildPHP\Core\Connection\IRCMessages\PONG;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\IRCMessages\QUIT;
use WildPHP\Core\Connection\IRCMessages\RAW;
use WildPHP\Core\Connection\IRCMessages\TOPIC;
use WildPHP\Core\Connection\IRCMessages\USER;
use WildPHP\Core\Connection\IRCMessages\WHO;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Logger\Logger;

class Queue implements QueueInterface
{
	use ComponentTrait;
	use ContainerTrait;

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
	 * This is disabled by default to allow for fast registration. It will be automatically enabled
	 * once the bot has been registered on the network.
	 *
	 * @var bool
	 */
	protected $floodControlEnabled = false;

	/**
	 * Queue constructor.
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);
	}

	/**
	 * @param BaseMessage $command
	 */
	public function insertMessage(BaseMessage $command)
	{
		$time = $this->calculateNextMessageTime();

		if ($time > time())
			Logger::fromContainer($this->getContainer())
				->warning('Throttling in effect. There are ' . ($this->getAmountOfItemsInQueue() + 1) . ' messages in the queue.');

		$item = new QueueItem($command, $time);
		$this->scheduleItem($item);
	}

	/**
	 * @param BaseMessage $command
	 */
	public function removeMessage(BaseMessage $command)
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
	    $privmsg = new PRIVMSG($channel, $message);
		$this->insertMessage($privmsg);
	}

	/**
	 * @param string $nickname
	 */
	public function nick(string $nickname)
	{
	    $nick = new NICK($nickname);
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
	    $user = new USER($username, $hostname, $servername, $realname);
		$this->insertMessage($user);
	}

	/**
	 * @param string $server1
	 * @param string $server2
	 */
	public function pong(string $server1, string $server2 = '')
	{
		$pong = new PONG($server1, $server2);
		$this->insertMessage($pong);
	}

    /**
     * @param string $server1
     * @param string $server2
     */
	public function ping(string $server1, string $server2 = '')
	{
	    $ping = new PING($server1, $server2);
		$this->insertMessage($ping);
	}

	/**
	 * @param array|string $channel
	 * @param string $key
	 */
	public function join($channel, $key = '')
	{
	    $join = new JOIN($channel, $key);
		$this->insertMessage($join);
	}

	/**
	 * @param array|string $channel
	 */
	public function part($channel)
	{
	    $part = new PART($channel);
		$this->insertMessage($part);
	}

    /**
     * @param string $command
     * @param array $capabilities
     */
	public function cap(string $command, array $capabilities = [])
	{
		$cap = new CAP($command, $capabilities);
		$this->insertMessage($cap);
	}

	/**
	 * @param string $channel
	 * @param string $options
	 */
	public function who(string $channel, string $options = '')
	{
		$who = new WHO($channel, $options);
		$this->insertMessage($who);
	}

	/**
	 * @param string $message
	 */
	public function quit(string $message)
	{
	    $quit = new QUIT($message);
		$this->insertMessage($quit);
	}

	/**
	 * @param string $channelName
	 * @param string $nickname
	 * @param string $message
	 */
	public function kick(string $channelName, string $nickname, string $message = '')
	{
	    $kick = new KICK($channelName, $nickname, $message);
		$this->insertMessage($kick);
	}

	/**
	 * @param string $target
	 * @param string $flags
	 * @param array $args
	 */
	public function mode(string $target, string $flags, array $args)
	{
	    $mode = new MODE($target, $flags, $args);
		$this->insertMessage($mode);
	}

	/**
	 * @param string $channelName
	 * @param string $message
	 */
	public function topic(string $channelName, string $message)
	{
		$topic = new TOPIC($channelName, $message);
		$this->insertMessage($topic);
	}

	/**
	 * @param string $raw
	 */
	public function raw(string $raw)
	{
	    $raw = new RAW($raw);
		$this->insertMessage($raw);
	}
}