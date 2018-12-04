<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;


use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Channels\ChannelModes;
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Core\Database\Database;
use WildPHP\Core\StateException;
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
     * @var Database
     */
    private $database;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param Database $database
     * @param LoggerInterface $logger
     */
    public function __construct(EventEmitterInterface $eventEmitter, Database $database, LoggerInterface $logger)
    {

        $eventEmitter->on('irc.line.in.join', [$this, 'processUserJoin']);
        $eventEmitter->on('irc.line.in.nick', [$this, 'processUserNickChange']);

        // 353: RPL_NAMREPLY
        $eventEmitter->on('irc.line.in.353', [$this, 'processNamesReply']);

        // 366: RPL_ENDOFNAMES
        $eventEmitter->on('irc.line.in.366', [$this, 'sendInitialWhoxMessage']);

        // 354: RPL_WHOSPCRPL
        $eventEmitter->on('irc.line.in.354', [$this, 'processWhoxReply']);

        $this->eventEmitter = $eventEmitter;
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * @param JOIN $joinMessage
     */
    public function processUserJoin(JOIN $joinMessage)
    {
        $db = $this->database;

        if (!$db->has('users', ['nickname' => $joinMessage->getNickname()])) {
            $prefix = $joinMessage->getPrefix();
            $db->insert('users', [
                'nickname' => $joinMessage->getNickname(),
                'username' => $prefix->getUsername(),
                'hostname' => $prefix->getHostname(),
                'irc_account' => $joinMessage->getIrcAccount(),
            ]);

            $this->logger->debug('Added user', [
                'reason' => 'join',
                'nickname' => $joinMessage->getNickname(),
                'username' => $prefix->getUsername(),
                'hostname' => $prefix->getHostname(),
                'irc_account' => $joinMessage->getIrcAccount()
            ]);
        }
    }

    /**
     * @param NamReply $ircMessage
     * @throws StateException
     */
    public function processNamesReply(NamReply $ircMessage)
    {
        $db = $this->database;
        $nicknames = $ircMessage->getNicknames();

        $modePrefixes = ($result = $db->get('server_config', ['value'],
            ['key' => 'PREFIX'])) ? $result['value'] : '(ohv)@%+';

        foreach ($nicknames as $nicknameWithMode) {
            $nickname = '';
            $modes = ChannelModes::extractUserModesFromNickname($modePrefixes, $nicknameWithMode, $nickname);

            if (!$db->has('users', ['nickname' => $nickname])) {
                $db->insert('users', [
                    'nickname' => $nickname
                ]);
                $userID = $db->id();
            } else {
                $userID = $db->get('users', ['id'], ['nickname' => $nickname])['id'];
            }

            if (!$db->has('channels', ['name' => $ircMessage->getChannel()])) {
                throw new StateException('Channel not found, but RPL_NAMES received; state mismatch!');
            }

            $channelID = $db->get('channels', ['id'], ['name' => $ircMessage->getChannel()])['id'];

            foreach ($modes as $mode) {
                $db->insert('mode_relations', [
                    'user_id' => $userID,
                    'channel_id' => $channelID,
                    'mode' => $mode
                ]);
            }

            $this->logger->debug('Modified or created user',
                ['reason' => 'rpl_namreply', 'nickname' => $nickname, 'modes' => $modes]);
        }
    }

    /**
     * @param EndOfNames $ircMessage
     * @param QueueInterface $queue
     */
    public function sendInitialWhoxMessage(EndOfNames $ircMessage, QueueInterface $queue)
    {
        $channel = $ircMessage->getChannel();
        $queue->who($channel, '%nuhaf');
    }

    /**
     * @param WhosPcRpl $ircMessage
     * @throws StateException
     * @throws UserNotFoundException
     */
    public function processWhoxReply(WhosPcRpl $ircMessage)
    {
        $db = $this->database;

        if (!$db->has('users', ['nickname' => $ircMessage->getNickname()])) {
            throw new StateException('RPL_WHOSPCRPL received but user was not found... Impossible!');
        }

        $user = User::fromDatabase($db, ['nickname' => $ircMessage->getNickname()]);
        $user->setNickname($ircMessage->getNickname());
        $user->setUsername($ircMessage->getUsername());
        $user->setHostname($ircMessage->getHostname());
        $user->setIrcAccount($ircMessage->getAccountname());
        User::toDatabase($db, $user);

        $this->logger->debug('Modified user',
            array_merge(['reason' => 'rpl_whospcrpl'], $user->toArray()));
    }

    /**
     * @param NICK $nickMessage
     * @throws StateException
     */
    public function processUserNickChange(NICK $nickMessage)
    {
        $db = $this->database;

        $userID = $db->get('users', ['id'], ['nickname' => $nickMessage->getNickname()]);

        if (!$userID) {
            throw new StateException('Nick change detected but I have no idea who this user is... Help!');
        }

        $db->update('users', [
            'nickname' => $nickMessage->getNewNickname()
        ], ['id' => $userID]);

        $this->logger->debug('Updated user nickname', [
            'oldNickname' => $nickMessage->getNickname(),
            'nickname' => $nickMessage->getNewNickname()
        ]);
    }
}