<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Channels;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Database\Database;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\StateException;
use WildPHP\Messages\Join;
use WildPHP\Messages\Kick;
use WildPHP\Messages\Part;
use WildPHP\Messages\Quit;
use WildPHP\Messages\RPL\NamReply;
use WildPHP\Messages\RPL\Topic;
use WildPHP\Messages\RPL\Welcome;

class ChannelObserver extends BaseModule
{
    /**
     * BaseModule constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        $this->setContainer($container);

        // 001: RPL_WELCOME
        EventEmitter::fromContainer($container)->on(
            'irc.line.in.001', [$this, 'joinInitialChannels']);

        EventEmitter::fromContainer($container)->on(
            'irc.line.in.join', [$this, 'createChannel']);

        EventEmitter::fromContainer($container)->on(
            'irc.line.in.join', [$this, 'processChannelJoin']);

        // 332: RPL_TOPIC
        EventEmitter::fromContainer($container)->on(
            'irc.line.in.332', [$this, 'processTopic']);

        // 353: RPL_NAMREPLY
        EventEmitter::fromContainer($container)->on(
            'irc.line.in.353', [$this, 'processNamesReply']);

        EventEmitter::fromContainer($container)->on(
            'irc.line.in.kick', [$this, 'processUserKick']);

        EventEmitter::fromContainer($container)->on(
            'irc.line.in.part', [$this, 'processUserPart']);

        EventEmitter::fromContainer($container)->on(
            'irc.line.in.quit', [$this, 'processUserQuit']);
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Welcome $incomingIrcMessage
     * @param Queue $queue
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function joinInitialChannels(Welcome $incomingIrcMessage, Queue $queue)
    {
        $channels = Configuration::fromContainer($this->getContainer())['channels'];

        if (empty($channels)) {
            return;
        }

        $chunks = array_chunk($channels, 3);
        $queue->setFloodControl(true);

        foreach ($chunks as $chunk) {
            $queue->join($chunk);
        }

        Logger::fromContainer($this->getContainer())
            ->debug('Queued initial channel join.',
                [
                    'count' => count($channels),
                    'channels' => $channels
                ]);
    }

    /**
     * @param JOIN $joinMessage
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function createChannel(JOIN $joinMessage)
    {
        $db = Database::fromContainer($this->getContainer());

        foreach ($joinMessage->getChannels() as $channel) {
            if (!$db->has('channels', ['name' => $channel])) {
                $db->insert('channels', [
                    'name' => $channel
                ]);

                $channelID = $db->id();

                Logger::fromContainer($this->getContainer())->debug('Created new channel', [
                    'id' => $channelID,
                    'name' => $channel
                ]);
            }
        }
    }

    /**
     * @param JOIN $joinMessage
     * @throws StateException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function processChannelJoin(JOIN $joinMessage)
    {
        $db = Database::fromContainer($this->getContainer());

        $row = $db->get('users', ['id'], ['nickname' => $joinMessage->getNickname()]);

        if (!$row) {
            throw new StateException('State mismatch! User not found in users table but join message received.');
        }

        $userID = $row['id'];

        foreach ($joinMessage->getChannels() as $channel) {
            $channelID = $db->get('channels', ['id'], ['name' => $channel])['id'];

            if ($db->has('user_channel_relationships', ['channel_id' => $channelID, 'user_id' => $userID])) {
                throw new StateException('User just joined channel, but already has a relation... Makes no sense!');
            }

            $db->insert('user_channel_relationships', ['channel_id' => $channelID, 'user_id' => $userID]);

            Logger::fromContainer($this->getContainer())->debug('Creating user relationship', [
                'reason' => 'join',
                'userID' => $userID,
                'nickname' => $joinMessage->getNickname(),
                'channelID' => $channelID,
                'channel' => $channel
            ]);
        }
    }

    /**
     * @param Topic $topicMessage
     * @throws StateException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function processTopic(Topic $topicMessage)
    {
        $db = Database::fromContainer($this->getContainer());

        $row = $db->get('channels', ['id'], ['name' => $topicMessage->getChannel()]);

        if (!$row) {
            throw new StateException('State mismatch! Channel not found in users table but topic received.');
        }

        $channelID = $row['id'];

        $db->update('channels', [
            'topic' => $topicMessage->getMessage()
        ], ['id' => $channelID]);

        Logger::fromContainer($this->getContainer())->debug('Updated topic', [
            'channel' => $topicMessage->getChannel(),
            'topic' => $topicMessage->getMessage()
        ]);
    }

    /**
     * @param NamReply $ircMessage
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws StateException
     */
    public function processNamesReply(NamReply $ircMessage)
    {
        $db = Database::fromContainer($this->getContainer());
        $nicknames = $ircMessage->getNicknames();

        $modePrefixes = ($result = $db->get('server_config', ['value'],
            ['key' => 'PREFIX'])) ? $result['value'] : '(ohv)@%+';

        foreach ($nicknames as $nicknameWithMode) {
            $nickname = '';
            ChannelModes::extractUserModesFromNickname($modePrefixes, $nicknameWithMode, $nickname);

            Logger::fromContainer($this->getContainer())->debug('Adding user to channel',
                ['reason' => 'rpl_namreply', 'nickname' => $nickname, 'channel' => $ircMessage->getChannel()]);

            $channelID = $db->get('channels', ['id'], ['name' => $ircMessage->getChannel()])['id'];
            $userID = $db->get('users', ['id'], ['nickname' => $nickname])['id'];

            if (!$channelID || !$userID) {
                throw new StateException('User or channel not found while they should be present during rpl_namreply, state mismatch!');
            }

            // this could be the bot itself; silently ignore this time.
            if ($db->has('user_channel_relationships', ['channel_id' => $channelID, 'user_id' => $userID])) {
                continue;
            }

            $db->insert('user_channel_relationships', ['channel_id' => $channelID, 'user_id' => $userID]);

            Logger::fromContainer($this->getContainer())->debug('Creating user relationship', [
                'reason' => 'rpl_namreply',
                'userID' => $userID,
                'nickname' => $nickname,
                'channelID' => $channelID,
                'channel' => $ircMessage->getChannel()
            ]);
        }
    }

    /**
     * @param KICK $kickMessage
     * @throws StateException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function processUserKick(KICK $kickMessage)
    {
        $db = Database::fromContainer($this->getContainer());

        $userID = $db->get('users', ['id'], ['nickname' => $kickMessage->getNickname()])['id'];

        if (!$userID) {
            throw new StateException('Kick detected but user not in database...I need help here!');
        }

        $channelID = $db->get('channels', ['id'], ['name' => $kickMessage->getChannel()])['id'];

        if (!$userID) {
            throw new StateException('Kick detected but channel not in database...I need help here!');
        }

        $db->delete('user_channel_relationships', ['user_id' => $userID, 'channel_id' => $channelID]);
        Logger::fromContainer($this->getContainer())->debug('Deleted user relationship', [
            'nickname' => $kickMessage->getNickname(),
            'channel' => $kickMessage->getChannel()
        ]);

        if ($db->count('user_channel_relationships', ['user_id' => $userID]) === 0) {
            $db->delete('mode_relations', ['user_id' => $userID]);
            $db->delete('users', ['user_id' => $userID]);

            Logger::fromContainer($this->getContainer())->debug('Deleted user from state because they are no longer in any mutual channels',
                [
                    'nickname' => $kickMessage->getNickname()
                ]);
        }
    }

    /**
     * @param PART $partMessage
     * @throws StateException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function processUserPart(PART $partMessage)
    {
        $db = Database::fromContainer($this->getContainer());

        $userID = $db->get('users', ['id'], ['nickname' => $partMessage->getNickname()])['id'];

        if (!$userID) {
            throw new StateException('Part detected but user not in database...I need help here!');
        }

        foreach ($partMessage->getChannels() as $channel) {
            $channelID = $db->get('channels', ['id'], ['name' => $channel])['id'];

            if (!$userID) {
                throw new StateException('Part detected but channel not in database...I need help here!');
            }

            $db->delete('user_channel_relationships', ['user_id' => $userID, 'channel_id' => $channelID]);
            Logger::fromContainer($this->getContainer())->debug('Deleted user relationship', [
                'nickname' => $partMessage->getNickname(),
                'channel' => $channel
            ]);
        }

        if ($db->count('user_channel_relationships', ['user_id' => $userID]) === 0) {
            $db->delete('mode_relations', ['user_id' => $userID]);
            $db->delete('users', ['user_id' => $userID]);

            Logger::fromContainer($this->getContainer())->debug('Deleted user from state because they are no longer in any mutual channels',
                [
                    'nickname' => $partMessage->getNickname()
                ]);
        }
    }

    /**
     * @param QUIT $quitMessage
     * @throws StateException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function processUserQuit(QUIT $quitMessage)
    {
        $db = Database::fromContainer($this->getContainer());

        $userID = $db->get('users', ['id'], ['nickname' => $quitMessage->getNickname()])['id'];

        if (!$userID) {
            throw new StateException('Quit detected but user not in database...can somebody explain this to me?');
        }

        $db->delete('user_channel_relationships', ['user_id' => $userID]);
        $db->delete('mode_relations', ['user_id' => $userID]);
        $db->delete('users', ['user_id' => $userID]);

        Logger::fromContainer($this->getContainer())->debug('Removed user from state', [
            'nickname' => $quitMessage->getNickname()
        ]);
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}
