<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Users;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\BaseModule;

class BotStateManager extends BaseModule
{
    use ContainerTrait;

    /**
     * BotStateManager constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        EventEmitter::fromContainer($container)
            ->on('user.nick', [$this, 'monitorOwnNickname']);
        $this->setContainer($container);
    }

    /** @noinspection PhpUnusedParameterInspection */
    /**
     * @param Channel $channel
     * @param User $user
     * @param string $oldNickname
     * @param string $newNickname
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function monitorOwnNickname(Channel $channel, User $user, string $oldNickname, string $newNickname)
    {
        if ($oldNickname != Configuration::fromContainer($this->getContainer())['currentNickname']) {
            return;
        }

        Configuration::fromContainer($this->getContainer())['currentNickname'] = $newNickname;

        Logger::fromContainer($this->getContainer())->debug('Updated current nickname for bot', [
            'oldNickname' => $oldNickname,
            'newNickname' => $newNickname
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