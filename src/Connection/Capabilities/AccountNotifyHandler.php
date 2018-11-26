<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Database\Database;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;
use WildPHP\Messages\Account;

class AccountNotifyHandler extends BaseModule
{
    use ContainerTrait;

    /**
     * AccountNotifyHandler constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        EventEmitter::fromContainer($container)->on('irc.line.in.account', [$this, 'updateUserIrcAccount']);
        $this->setContainer($container);
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param ACCOUNT $ircMessage
     * @throws \WildPHP\Core\StateException
     * @throws \WildPHP\Core\Users\UserNotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function updateUserIrcAccount(ACCOUNT $ircMessage)
    {
        $nickname = $ircMessage->getPrefix()->getNickname();
        $db = Database::fromContainer($this->getContainer());

        $user = User::fromDatabase($db, ['nickname' => $nickname]);
        Logger::fromContainer($this->getContainer())->debug('Updated irc account for userid ' . $user->getId());
        $user->setIrcAccount($ircMessage->getAccountName());

        User::toDatabase($db, $user);
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }

    /**
     * @return array
     */
    public static function getDependentModules(): array
    {
        return [
            EventEmitter::class,
            Database::class,
            Logger::class
        ];
    }
}