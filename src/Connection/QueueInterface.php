<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use WildPHP\Messages\Interfaces\OutgoingMessageInterface;

/**
 * Class Queue
 * @package WildPHP\Core\Connection
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
interface QueueInterface
{
    /**
     * @param QueueItem $item
     *
     * @return bool
     */
    public function removeMessage(QueueItem $item);

    /**
     * @param int $index
     *
     * @return bool
     */
    public function removeMessageByIndex(int $index);

    /**
     * @return QueueItem[]
     */
    public function getDueItems(): array;

    /**
     * @param QueueItem[] $queueItems
     */
    public function processQueueItems(array $queueItems);

    public function processQueueItem(QueueItem $queueItem);

    /**
     * @param bool $enabled
     */
    public function setFloodControl(bool $enabled = true);

    /**
     * @param OutgoingMessageInterface $command
     *
     * @return QueueItem
     */
    public function insertMessage(OutgoingMessageInterface $command): QueueItem;

    /**
     * @param QueueItem $item
     */
    public function scheduleItem(QueueItem $item);

    /**
     * @return int
     */
    public function calculateNextMessageTimestamp(): int;

    /**
     * @return bool
     */
    public function isFloodControlEnabled(): bool;
}