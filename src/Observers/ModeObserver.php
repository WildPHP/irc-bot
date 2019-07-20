<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Observers;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use WildPHP\Core\Events\IncomingIrcMessageEvent;
use WildPHP\Core\Storage\IrcChannelStorageInterface;
use WildPHP\Core\Storage\IrcUserChannelRelationStorageInterface;
use WildPHP\Core\Storage\IrcUserStorageInterface;
use WildPHP\Messages\Mode;
use WildPHP\Messages\RPL\MyInfo;

class ModeObserver
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
     * @var array
     */
    private $userModes = [];

    /**
     * @var array
     */
    private $channelModes = [];

    /**
     * @var array
     */
    private $channelModesWithParameter = [];

    /**
     * ModeObserver constructor.
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
        $eventEmitter->on('irc.msg.in.004', [$this, 'createModeDefinitions']);
        $eventEmitter->on('irc.msg.in.mode', [$this, 'handleModeMessage']);

        $this->logger = $logger;
        $this->channelStorage = $channelStorage;
        $this->userStorage = $userStorage;
        $this->relationStorage = $relationStorage;
    }

    /**
     * @param IncomingIrcMessageEvent $event
     */
    public function handleModeMessage(IncomingIrcMessageEvent $event): void
    {
        /** @var Mode $message */
        $message = $event->getIncomingMessage();

        $targetString = $message->getTarget();
        $flags = str_split($message->getFlags());
        $args = $message->getArguments();

        $add = array_shift($flags) === '+';

        // check if the target is a user. easy-mode.
        $user = $this->userStorage->getOneByNickname($targetString);

        if ($user !== null) {
            foreach ($flags as $mode) {
                if ($add) {
                    $user->getModes()->addMode($mode);
                } elseif ($user->getModes()->hasMode($mode)) {
                    $user->getModes()->removeMode($mode);
                }
            }

            $this->logger->debug('Changed modes for user', [
                'userID' => $user->getUserId(),
                'nickname' => $user->getNickname(),
                'modes' => $flags,
                'added' => $add,
                'currentModes' => $user->getModes()->toArray()
            ]);
            $this->userStorage->store($user);

            return;
        }

        $channel = $this->channelStorage->getOneByName($targetString);

        if ($channel === null) {
            throw new RuntimeException('Unable to change mode for this entity because it is unknown');
        }

        $parameterIndex = 0;
        foreach ($flags as $flag) {
            if (in_array($flag, $this->channelModesWithParameter, true)) {
                $userInChannel = $this->userStorage->getOneByNickname($args[$parameterIndex]);

                $entityModes = $userInChannel !== null
                    ? $channel->getModesForUserId($userInChannel->getUserId())
                    : $channel->getModes();

                if ($add) {
                    $entityModes->addMode($flag, ($userInChannel !== null ? true : $args[$parameterIndex]));
                } elseif ($entityModes->hasMode($flag)) {
                    $entityModes->removeMode($flag);
                }

                $this->logger->debug('Changed mode for user inside channel', [
                    'channelID' => $channel->getChannelId(),
                    'name' => $channel->getName(),
                    'userID' => $userInChannel->getUserId(),
                    'nickname' => $userInChannel->getNickname(),
                    'flag' => $flag,
                    'added' => $add
                ]);

                $parameterIndex++;
                continue;
            }

            if ($add) {
                $channel->getModes()->addMode($flag);
            } elseif ($channel->getModes()->hasMode($flag)) {
                $channel->getModes()->removeMode($flag);
            }

            $this->logger->debug('Changed mode for channel', [
                'channelID' => $channel->getChannelId(),
                'name' => $channel->getName(),
                'modes' => $flag,
                'added' => $add
            ]);
        }
        $this->channelStorage->store($channel);
    }

    public function createModeDefinitions(IncomingIrcMessageEvent $event): void
    {
        /** @var MyInfo $message */
        $message = $event->getIncomingMessage();

        $this->userModes = $message->getUserModes();
        $this->channelModes = $message->getChannelModes();
        $this->channelModesWithParameter = $message->getChannelModesWithParameter();

        $this->logger->debug('Updated mode definitions', [
            'userModes' => $this->userModes,
            'channelModes' => $this->channelModes,
            'channelModesWithParameter' => $this->channelModesWithParameter
        ]);
    }
}
