<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use Evenement\EventEmitterInterface;
use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

/**
 * Class Queue
 * @package WildPHP\Core\Observers
 *
 * Magic methods below
 * @method QueueItem authenticate(string $response)
 * @method QueueItem away(string $message)
 * @method QueueItem cap(string $command, array $capabilities = [])
 * @method QueueItem join(mixed $channels, array $keys = [])
 * @method QueueItem kick(string $channel, string $nickname, string $message)
 * @method QueueItem mode(string $target, string $flags, array $arguments = [])
 * @method QueueItem nick(string $newNickname)
 * @method QueueItem notice(string $channel, string $message)
 * @method QueueItem part(mixed $channels, $message = '')
 * @method QueueItem pass(string $password)
 * @method QueueItem ping(string $server1, string $server2 = '')
 * @method QueueItem pong(string $server1, string $server2 = '')
 * @method QueueItem privmsg(string $channel, string $message)
 * @method QueueItem quit(string $message)
 * @method QueueItem raw(string $command)
 * @method QueueItem remove(string $channel, string $nickname, string $message)
 * @method QueueItem topic(string $channelName, string $message)
 * @method QueueItem user(string $username, string $hostname, string $servername, string $realname)
 * @method QueueItem version(string $server = '')
 * @method QueueItem who(string $channel, string $options = '')
 * @method QueueItem whois(string[] | string $nicknames, string $server = '')
 * @method QueueItem whowas(string[] | string $nicknames, int $count = 0, string $server = '')
 *
 */
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
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * Queue constructor.
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(EventEmitterInterface $eventEmitter)
    {
        $this->eventEmitter = $eventEmitter;
    }


    /**
     * @param QueueItem $item
     *
     * @return bool
     */
    public function removeMessage(QueueItem $item)
    {
        if (!in_array($item, $this->messageQueue)) {
            return false;
        }

        return $this->removeMessageByIndex(array_search($item, $this->messageQueue));
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    public function removeMessageByIndex(int $index)
    {
        if (!array_key_exists($index, $this->messageQueue)) {
            return false;
        }

        unset($this->messageQueue[$index]);
        return true;
    }

    public function clear(): void
    {
        $this->messageQueue = [];
    }

    /**
     * @return QueueItem[]
     */
    public function getDueItems(): array
    {
        $expired = [];
        foreach ($this->messageQueue as $index => $queueItem) {
            if (!$queueItem->itemShouldBeTriggered()) {
                continue;
            }

            $expired[] = $queueItem;
            $this->removeMessageByIndex($index);
        }

        return $expired;
    }

    /**
     * @param QueueItem[] $queueItems
     */
    public function processQueueItems(array $queueItems)
    {
        /** @var QueueItem $item */
        foreach ($queueItems as $item) {
            $verb = strtolower($item->getCommandObject()::getVerb()) ?? '';
            $this->eventEmitter->emit('irc.line.out', [$item]);
            $this->eventEmitter->emit('irc.line.out.' . $verb, [$item]);
        }
    }

    public function processQueueItem(QueueItem $queueItem): void
    {
        $verb = strtolower($queueItem->getCommandObject()::getVerb());
        $this->eventEmitter->emit('irc.line.out', [$queueItem]);
        $this->eventEmitter->emit('irc.line.out.' . $verb, [$queueItem]);
    }

    /**
     * @param bool $enabled
     */
    public function setFloodControl(bool $enabled = true)
    {
        $this->floodControlEnabled = $enabled;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return QueueItem
     */
    public function __call(string $name, array $arguments)
    {
        $class = '\\WildPHP\\Messages\\' . ucfirst($name);
        if (!class_exists($class)) {
            throw new \RuntimeException('Cannot send message of type ' . $class . '; no message of such type found.');
        }

        $object = new $class(...$arguments);
        return $this->insertMessage($object);
    }

    /**
     * @param OutgoingMessageInterface $command
     *
     * @return QueueItem
     */
    public function insertMessage(OutgoingMessageInterface $command): QueueItem
    {
        $time = $this->calculateNextMessageTimestamp();
        $item = new QueueItem($command, $time);
        $this->scheduleItem($item);
        return $item;
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
    public function calculateNextMessageTimestamp(): int
    {
        // If the queue is empty, this message can be sent immediately. Do not bother calculating.
        if ($this->count() < $this->floodControlMessageThreshold || !$this->isFloodControlEnabled()) {
            return time();
        }

        $numItems = $this->count() - $this->floodControlMessageThreshold;
        $messagePairs = (int) round($numItems / $this->messagesPerSecond, 0, PHP_ROUND_HALF_DOWN);

        // For every message pair, we add the specified delay.
        $totalDelay = $messagePairs * $this->messageDelayInSeconds;

        return time() + $totalDelay;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->messageQueue);
    }

    /**
     * @return bool
     */
    public function isFloodControlEnabled(): bool
    {
        return $this->floodControlEnabled;
    }
}