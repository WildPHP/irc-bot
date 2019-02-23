<?php
declare(strict_types=1);

/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Queue;

/**
 * Class Queue
 * @package WildPHP\Core\Observers
 *
 * Magic methods below
 * @method IrcMessageQueueItem authenticate(string $response)
 * @method IrcMessageQueueItem away(string $message)
 * @method IrcMessageQueueItem cap(string $command, array $capabilities = [])
 * @method IrcMessageQueueItem join(mixed $channels, array $keys = [])
 * @method IrcMessageQueueItem kick(string $channel, string $nickname, string $message)
 * @method IrcMessageQueueItem mode(string $target, string $flags, array $arguments = [])
 * @method IrcMessageQueueItem nick(string $newNickname)
 * @method IrcMessageQueueItem notice(string $channel, string $message)
 * @method IrcMessageQueueItem part(mixed $channels, $message = '')
 * @method IrcMessageQueueItem pass(string $password)
 * @method IrcMessageQueueItem ping(string $server1, string $server2 = '')
 * @method IrcMessageQueueItem pong(string $server1, string $server2 = '')
 * @method IrcMessageQueueItem privmsg(string $channel, string $message)
 * @method IrcMessageQueueItem quit(string $message)
 * @method IrcMessageQueueItem raw(string $command)
 * @method IrcMessageQueueItem remove(string $channel, string $nickname, string $message)
 * @method IrcMessageQueueItem topic(string $channelName, string $message)
 * @method IrcMessageQueueItem user(string $username, string $hostname, string $servername, string $realname)
 * @method IrcMessageQueueItem version(string $server = '')
 * @method IrcMessageQueueItem who(string $channel, string $options = '')
 * @method IrcMessageQueueItem whois(string[] | string $nicknames, string $server = '')
 * @method IrcMessageQueueItem whowas(string[] | string $nicknames, int $count = 0, string $server = '')
 *
 */
class IrcMessageQueue implements QueueInterface
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
     * @var IrcMessageQueueItem[]
     */
    protected $messageQueue = [];

    /**
     * @param QueueItemInterface $queueItem
     */
    public function enqueue(QueueItemInterface $queueItem): void
    {
        $this->messageQueue[] = $queueItem;
    }

    /**
     * @param QueueItemInterface $queueItem
     */
    public function dequeue(QueueItemInterface $queueItem): void
    {
        unset($this->messageQueue[array_search($queueItem, $this->messageQueue, true)]);
    }

    /**
     * @return QueueItemInterface[]
     */
    public function toArray(): array
    {
        return $this->messageQueue;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->messageQueue = [];
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return IrcMessageQueueItem
     */
    public function __call(string $name, array $arguments): IrcMessageQueueItem
    {
        $class = '\\WildPHP\\Messages\\' . ucfirst($name);

        if (!class_exists($class)) {
            throw new \RuntimeException('Cannot send message of type ' . $class . '; no message of such type found.');
        }

        $object = new $class(...$arguments);
        $queueItem = new IrcMessageQueueItem($object);
        $this->enqueue($queueItem);
        return $queueItem;
    }
}