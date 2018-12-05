<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Observers;


use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Connection\QueueInterface;
use WildPHP\Core\Connection\UserModeParser;
use WildPHP\Core\Entities\Base\IrcChannelQuery;
use WildPHP\Core\Entities\Base\IrcUserQuery;
use WildPHP\Core\Entities\UserModeChannel;
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
     * @var QueueInterface
     */
    private $queue;

    /**
     * BaseModule constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param LoggerInterface $logger
     * @param QueueInterface $queue
     */
    public function __construct(EventEmitterInterface $eventEmitter, LoggerInterface $logger, QueueInterface $queue)
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
        $this->logger = $logger;
        $this->queue = $queue;
    }

    /**
     * @param JOIN $joinMessage
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function processUserJoin(JOIN $joinMessage)
    {
        $user = IrcUserQuery::create()->filterByNickname($joinMessage->getNickname())->findOneOrCreate();

        $prefix = $joinMessage->getPrefix();
        $user->setNickname($joinMessage->getNickname());
        $user->setUsername($prefix->getUsername());
        $user->setHostname($prefix->getHostname());
        $user->setIrcAccount($joinMessage->getIrcAccount());
        $user->save();

        if ($user->isNew())
            $this->logger->debug('Added user', [
                'reason' => 'join',
                'nickname' => $joinMessage->getNickname(),
                'username' => $prefix->getUsername(),
                'hostname' => $prefix->getHostname(),
                'irc_account' => $joinMessage->getIrcAccount()
            ]);
        else
            $this->logger->debug('Updated existing user', [
                'reason' => 'join',
                'nickname' => $joinMessage->getNickname(),
                'username' => $prefix->getUsername(),
                'hostname' => $prefix->getHostname(),
                'irc_account' => $joinMessage->getIrcAccount()
            ]);
    }

    /**
     * @param NamReply $ircMessage
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function processNamesReply(NamReply $ircMessage)
    {
        $nicknames = $ircMessage->getNicknames();

        foreach ($nicknames as $nicknameWithMode) {
            $nickname = '';
            $modes = UserModeParser::extractFromNickname($nicknameWithMode, $nickname);

            $user = IrcUserQuery::create()->filterByNickname($nickname)->findOneOrCreate();
            $channel = IrcChannelQuery::create()->findOneByName($ircMessage->getChannel());

            foreach ($modes as $mode) {
                $userChannelMode = new UserModeChannel();
                $userChannelMode->setIrcUser($user);
                $userChannelMode->setIrcChannel($channel);
                $userChannelMode->setMode($mode);
                $userChannelMode->save();
            }

            $this->logger->debug('Modified or created user',
                ['reason' => 'rpl_namreply', 'nickname' => $nickname, 'modes' => $modes]);
        }
    }

    /**
     * @param EndOfNames $ircMessage
     */
    public function sendInitialWhoxMessage(EndOfNames $ircMessage)
    {
        $channel = $ircMessage->getChannel();
        $this->queue->who($channel, '%nuhaf');
    }

    /**
     * @param WhosPcRpl $ircMessage
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function processWhoxReply(WhosPcRpl $ircMessage)
    {
        $user = IrcUserQuery::create()->filterByNickname($ircMessage->getNickname())->findOneOrCreate();
        $user->setNickname($ircMessage->getNickname());
        $user->setUsername($ircMessage->getUsername());
        $user->setHostname($ircMessage->getHostname());
        $user->setIrcAccount($ircMessage->getAccountname());
        $user->save();

        $this->logger->debug('Modified user',
            array_merge(['reason' => 'rpl_whospcrpl'], $user->toArray()));
    }

    /**
     * @param NICK $nickMessage
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function processUserNickChange(NICK $nickMessage)
    {
        $user = IrcUserQuery::create()->findOneByNickname($nickMessage->getNickname());
        $user->setNickname($nickMessage->getNewNickname());
        $user->save();

        $this->eventEmitter->emit('user.nick', [
            $user,
            $nickMessage->getNickname(),
            $nickMessage->getNewNickname()
        ]);

        $this->logger->debug('Updated user nickname', [
            'oldNickname' => $nickMessage->getNickname(),
            'nickname' => $nickMessage->getNewNickname()
        ]);
    }
}