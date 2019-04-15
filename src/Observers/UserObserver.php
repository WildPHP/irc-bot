<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use WildPHP\Core\Connection\UserModeParser;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Events\NicknameChangedEvent;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Join;
use WildPHP\Messages\Nick;
use WildPHP\Messages\RPL\EndOfNames;
use WildPHP\Messages\RPL\NamReply;
use WildPHP\Messages\RPL\WhosPcRpl;

class UserObserver
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IrcMessageQueue
     */
    private $queue;

    /**
     * @var IrcUserStorageInterface
     */
    private $userStorage;

    /**
     * @var IrcChannelStorageInterface
     */
    private $channelStorage;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param IrcMessageQueue $queue
     * @param IrcUserStorageInterface $userStorage
     * @param IrcChannelStorageInterface $channelStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        IrcMessageQueue $queue,
        IrcUserStorageInterface $userStorage,
        IrcChannelStorageInterface $channelStorage
    ) {
        $eventEmitter->on('irc.msg.in.join', [$this, 'processUserJoin']);
        $eventEmitter->on('irc.msg.in.nick', [$this, 'processUserNickChange']);

        // 353: RPL_NAMREPLY
        $eventEmitter->on('irc.msg.in.353', [$this, 'processNamesReply']);

        // 366: RPL_ENDOFNAMES
        $eventEmitter->on('irc.msg.in.366', [$this, 'sendInitialWhoxMessage']);

        // 354: RPL_WHOSPCRPL
        $eventEmitter->on('irc.msg.in.354', [$this, 'processWhoxReply']);

        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;
        $this->queue = $queue;
        $this->userStorage = $userStorage;
        $this->channelStorage = $channelStorage;
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserJoin(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Join $joinMessage */
        $joinMessage = $ircMessageEvent->getIncomingMessage();

        $user = $this->userStorage->getOrCreateOneByNickname($joinMessage->getNickname());

        $prefix = $joinMessage->getPrefix();
        $user->setUsername($prefix->getUsername());
        $user->setHostname($prefix->getHostname());
        $user->setIrcAccount($joinMessage->getIrcAccount());
        $this->userStorage->store($user);

        $this->logger->debug('Updated user', [
            'reason' => 'join',
            'id' => $user->getUserId(),
            'nickname' => $joinMessage->getNickname(),
            'username' => $prefix->getUsername(),
            'hostname' => $prefix->getHostname(),
            'irc_account' => $joinMessage->getIrcAccount()
        ]);
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processNamesReply(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var NamReply $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();

        $nicknames = $ircMessage->getNicknames();

        foreach ($nicknames as $nicknameWithMode) {
            $nickname = '';
            $modes = UserModeParser::extractFromNickname($nicknameWithMode, $nickname);

            $user = $this->userStorage->getOrCreateOneByNickname($ircMessage->getNickname());
            $channel = $this->channelStorage->getOneByName($ircMessage->getChannel());

            // TODO: REIMPLEMENT THIS
            /*foreach ($modes as $mode) {
                $userChannelMode = new UserModeChannel();
                $userChannelMode->setIrcUser($user);
                $userChannelMode->setIrcChannel($channel);
                $userChannelMode->setMode($mode);
                $userChannelMode->save();
            }*/

            $this->logger->debug(
                'Modified or created user',
                ['reason' => 'rpl_namreply', 'nickname' => $nickname, 'modes' => $modes]
            );
        }
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function sendInitialWhoxMessage(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var EndOfNames $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();

        $channel = $ircMessage->getChannel();
        $this->queue->who($channel, '%nuhaf');
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processWhoxReply(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var WhosPcRpl $ircMessage */
        $ircMessage = $ircMessageEvent->getIncomingMessage();

        $user = $this->userStorage->getOrCreateOneByNickname($ircMessage->getNickname());
        $user->setNickname($ircMessage->getNickname());
        $user->setUsername($ircMessage->getUsername());
        $user->setHostname($ircMessage->getHostname());
        $user->setIrcAccount($ircMessage->getAccountname());
        $this->userStorage->store($user);

        $this->logger->debug(
            'Modified user',
            array_merge(['reason' => 'rpl_whospcrpl'], $user->toArray())
        );
    }

    /**
     * @param IncomingIrcMessageEvent $ircMessageEvent
     */
    public function processUserNickChange(IncomingIrcMessageEvent $ircMessageEvent): void
    {
        /** @var Nick $nickMessage */
        $nickMessage = $ircMessageEvent->getIncomingMessage();

        $user = $this->userStorage->getOneByNickname($nickMessage->getNickname());

        if ($user === null) {
            throw new RuntimeException('No user found while one was expected');
        }

        $user->setNickname($nickMessage->getNewNickname());
        $this->userStorage->store($user);

        $this->eventEmitter->emit('user.nick', [
            new NicknameChangedEvent(
                $user,
                $nickMessage->getNickname(),
                $nickMessage->getNewNickname()
            )
        ]);

        $this->logger->debug('Updated user nickname', [
            'oldNickname' => $nickMessage->getNickname(),
            'nickname' => $nickMessage->getNewNickname()
        ]);
    }
}
