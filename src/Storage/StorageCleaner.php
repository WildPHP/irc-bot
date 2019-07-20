<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage;

use Psr\Log\LoggerInterface;
use WildPHP\Core\Entities\EntityModes;

class StorageCleaner
{
    /**
     * StorageCleaner constructor.
     * @param IrcUserStorageInterface $userStorage
     * @param IrcChannelStorageInterface $channelStorage
     * @param IrcUserChannelRelationStorageInterface $relationStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        IrcUserStorageInterface $userStorage,
        IrcChannelStorageInterface $channelStorage,
        IrcUserChannelRelationStorageInterface $relationStorage,
        LoggerInterface $logger
    ) {
        $logger->debug('Running cleanup tasks for the storage subsystem...');

        $logger->debug('Removing set modes for users and setting online flags...');
        $nicknames = [];
        foreach ($userStorage->getAll() as $user) {
            $logger->debug('Processing user...', [
                'id' => $user->getUserId(),
                'nickname' => $user->getNickname()
            ]);

            if (in_array($user->getNickname(), $nicknames, true)) {
                $logger->error('Found duplicate nickname. Removing duplicate user.');
                $userStorage->delete($user);
                continue;
            }

            if (empty($user->getHostname()) || empty($user->getUsername())) {
                $logger->error('Found user without hostname or username. Removing invalid user.');
                $userStorage->delete($user);
                continue;
            }

            $user->setModes(new EntityModes());
            $user->setOnline(false);
            $userStorage->store($user);
            $nicknames[] = $user->getNickname();
        }

        $logger->debug('Removing set modes & topics for channels...');
        foreach ($channelStorage->getAll() as $channel) {
            $logger->debug('Processing channel...', [
                'id' => $channel->getChannelId(),
                'name' => $channel->getName()
            ]);
            $channel->setTopic('');
            $channel->setModes(new EntityModes());

            $logger->debug('Removing user modes for this channel...', [
                'currentModeSetAmount' => count($channel->getUserModes())
            ]);
            $channel->setUserModes([]);
            $channelStorage->store($channel);
        }

        $logger->debug('Removing all prior user-channel relationships...');
        foreach ($relationStorage->getAll() as $relation) {
            $logger->debug('Deleting relation', [
                'userId' => $relation->getIrcUserId(),
                'channelId' => $relation->getIrcChannelId()
            ]);
            $relationStorage->delete($relation);
        }
    }
}
