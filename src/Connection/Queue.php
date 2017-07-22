<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\SendableMessage;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Logger\Logger;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

/**
 * Class Queue
 * @package WildPHP\Core\Connection
 *
 * Magic methods below
 * @method void authenticate(string $response)
 * @method void away(string $message)
 * @method void cap(string $command, array $capabilities = [])
 * @method void join(mixed $channels, array $keys = [])
 * @method void kick(string $channel, string $nickname, string $message)
 * @method void mode(string $target, string $flags, array $arguments = [])
 * @method void nick(string $newNickname)
 * @method void notice(string $channel, string $message)
 * @method void part(mixed $channels, $message = '')
 * @method void pass(string $password)
 * @method void ping(string $server1, string $server2 = '')
 * @method void pong(string $server1, string $server2 = '')
 * @method void privmsg(string $channel, string $message)
 * @method void quit(string $message)
 * @method void raw(string $command)
 * @method void remove(string $channel, string $nickname, string $message)
 * @method void topic(string $channelName, string $message)
 * @method void user(string $username, string $hostname, string $servername, string $realname)
 * @method void version(string $server = '')
 * @method void who(string $channel, string $options = '')
 * @method void whois(string[]|string $nicknames, string $server = '')
 * @method void whowas(string[]|string $nicknames, int $count = 0, string $server = '')
 *
 */
class Queue implements QueueInterface, ComponentInterface
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
	 * @var int
	 */
	protected $floodControlMessageThreshold = 5;

	/**
	 * This is disabled by default to allow for fast registration. It will be automatically enabled
	 * once the bot has been registered on the network.
	 *
	 * @var bool
	 */
	protected $floodControlEnabled = false;

	/**
	 * Queue constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);
	}

	/**
	 * @param SendableMessage $command
	 */
	public function insertMessage(SendableMessage $command)
	{
		$time = $this->calculateNextMessageTime();

		if ($time > time())
			Logger::fromContainer($this->getContainer())
				->warning('Throttling in effect. There are ' . ($this->getAmountOfItemsInQueue() + 1) . ' messages in the queue.');

		$item = new QueueItem($command, $time);
		$this->scheduleItem($item);
	}

	/**
	 * @param SendableMessage $command
	 *
	 * @return bool
	 */
	public function removeMessage(SendableMessage $command)
	{
		if (!in_array($command, $this->messageQueue))
			return false;

		return $this->removeMessageByIndex(array_search($command, $this->messageQueue));
	}

	/**
	 * @param int $index
	 *
	 * @return bool
	 */
	public function removeMessageByIndex(int $index)
	{
		if (!array_key_exists($index, $this->messageQueue))
			return false;

		unset($this->messageQueue[$index]);
		return true;
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
		if ($this->getAmountOfItemsInQueue() < $this->floodControlMessageThreshold || !$this->isFloodControlEnabled())
			return time();

		$numItems = $this->getAmountOfItemsInQueue();
		$numItems = $numItems - $this->floodControlMessageThreshold;
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

	public function clear()
	{
		$this->messageQueue = [];
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
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call(string $name, array $arguments)
	{
		$class = '\WildPHP\Core\Connection\IRCMessages\\' . strtoupper($name);
		if (!class_exists($class))
			throw new \RuntimeException('Cannot send message of type ' . $class . '; no message of such type found.');

		$object = new $class(...$arguments);
		$this->insertMessage($object);
	}
}