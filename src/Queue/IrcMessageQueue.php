<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

use Evenement\EventEmitterInterface;
use WildPHP\Core\Connection\IrcConnectionInterface;
use WildPHP\Core\Events\OutgoingIrcMessageEvent;
use WildPHP\Messages\Interfaces\OutgoingMessageInterface;
use WildPHP\Queue\BaseQueue;
use WildPHP\Queue\QueueException;

/**
 * Class Queue
 * @package WildPHP\Core\Observers
 *
 * Magic methods below
 * TODO: Update this list.
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
class IrcMessageQueue extends BaseQueue
{
    /**
     * @var IrcConnectionInterface
     */
    private $ircConnection;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * IrcMessageQueue constructor.
     * @param IrcConnectionInterface $ircConnection
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(IrcConnectionInterface $ircConnection, EventEmitterInterface $eventEmitter)
    {
        $this->ircConnection = $ircConnection;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return IrcMessageQueueItem
     * @throws QueueException
     */
    public function __call(string $name, array $arguments): IrcMessageQueueItem
    {
        $class = '\\WildPHP\\Messages\\' . ucfirst($name);

        if (!class_exists($class)) {
            throw new QueueException('Cannot send message of type ' . $class . '; no message of such type found.');
        }

        $object = new $class(...$arguments);
        $queueItem = new IrcMessageQueueItem($object);
        $promise = $this->enqueue($queueItem);

        $promise->then(function (IrcMessageQueueItem $queueItem) {
            $this->writeMessage($queueItem->getOutgoingMessage());
        });

        return $queueItem;
    }

    /**
     * @param OutgoingMessageInterface $outgoingMessage
     */
    public function writeMessage(OutgoingMessageInterface $outgoingMessage): void
    {
        $this->ircConnection->write($outgoingMessage->__toString());

        $event = new OutgoingIrcMessageEvent($outgoingMessage);
        $this->eventEmitter->emit('irc.msg.out', [$event]);
        $this->eventEmitter->emit('irc.msg.out.' . strtolower($outgoingMessage::getVerb()), [$event]);
    }
}
