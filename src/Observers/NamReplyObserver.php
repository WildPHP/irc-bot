<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Generics\Prefix;
use WildPHP\Messages\RPL\NamReply;

class NamReplyObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * @var IrcUserChannelRelationStorageInterface
     */
    private $relationStorage;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcChannelStorageInterface $channelStorage
     * @param IrcUserStorageInterface $userStorage
     * @param IrcUserChannelRelationStorageInterface $relationStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcChannelStorageInterface $channelStorage,
        IrcUserStorageInterface $userStorage,
        IrcUserChannelRelationStorageInterface $relationStorage
    ) {
        // 353: RPL_NAMREPLY
        $eventEmitter->on('irc.msg.in.353', [$this, 'processNamesReply']);

        $this->logger = $logger;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->relationStorage = $relationStorage;
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processNamesReply(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var NamReply $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();
        $nicknames = $ircMessage->getNicknames();

        /** @var Prefix[] $prefixes */
        $prefixes = $ircMessage->getPrefixes();

        $channel = $this->channelStorage->getOrCreateOneByName($ircMessage->getChannel());

        foreach ($nicknames as $nickname) {
            $user = $this->userStorage->getOrCreateOneByNickname($nickname);

            if (!$user->online) {
                $user->online = true;
                $this->userStorage->store($user);

                $this->logger->debug('Set online flag for user', [
                    'reason' => 'RPL_NAMREPLY',
                    'id' => $user->id,
                    'nickname' => $user->nickname,
                    'newValue' => $user->online
                ]);
            }

            $modes = $ircMessage->getModes()[$nickname];
            foreach ($modes as $mode) {
                $this->logger->debug('Added user to mode', [
                    'userID' => $user->id,
                    'nickname' => $user->nickname,
                    'mode' => $mode
                ]);
                $channel->getModesForUserId($user->id)->addMode($mode);
            }

            // userhost-in-names support
            if (array_key_exists($nickname, $prefixes)) {
                $prefix = $prefixes[$nickname];
                $user->hostname = $prefix->getHostname();
                $user->username = $prefix->getUsername();
                $this->userStorage->store($user);
            }

            $relation = $this->relationStorage->getOrCreateOne(
                $user->id,
                $channel->id
            );

            $this->logger->debug('Creating user-channel relationship', [
                'reason' => 'rpl_namreply',
                'userID' => $relation->ircUserId,
                'nickname' => $user->nickname,
                'channelID' => $relation->ircChannelId,
                'channel' => $channel->name
            ]);
        }

        $this->channelStorage->store($channel);
    }
}
