<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;


use WildPHP\Commands\Command;
use WildPHP\Commands\Parameters\StringParameter;
use WildPHP\Commands\ParameterStrategy;
use WildPHP\Core\Commands\CommandRegistrar;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Database\Database;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Observers\Channel;
use WildPHP\Core\Observers\Queue;
use WildPHP\Core\Observers\User;
use WildPHP\Core\Observers\UserNotFoundException;

class PermissionMembersCommands extends BaseModule
{
    /**
     * PermissionCommands constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        $permissionGroupCollection = PermissionGroupCollection::fromContainer($container);

        CommandRegistrar::fromContainer($container)->register('lsmembers',
            new Command(
                [$this, 'lsmembersCommand'],
                new ParameterStrategy(1, 1, [
                    'group' => new GroupParameter($permissionGroupCollection)
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('addmember',
            new Command(
                [$this, 'addmemberCommand'],
                new ParameterStrategy(2, 2, [
                    'group' => new GroupParameter($permissionGroupCollection),
                    'nickname' => new StringParameter()
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('delmember',
            new Command(
                [$this, 'delmemberCommand'],
                new ParameterStrategy(2, 2, [
                    'group' => new GroupParameter($permissionGroupCollection),
                    'nickname' => new StringParameter()
                ])
            ));

        $this->setContainer($container);
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function lsmembersCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];

        $checks = [
            'This group does not exist.' => empty($group),
            'This group may not contain members.' => $group ? $group->isModeGroup() : false
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $members = $group->getUserCollection()
            ->values();
        Queue::fromContainer($container)
            ->privmsg($source->getName(), sprintf('%s: The following members are in this group: %s',
                $user->getNickname(),
                implode(', ', $members)));
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \WildPHP\Core\StateException
     * @throws \WildPHP\Core\Observers\UserNotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function addmemberCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        $db = Database::fromContainer($this->getContainer());
        $nickname = $args['nickname'];

        /** @var PermissionGroup $group */
        $group = $args['group'];

        /** @var User $userToAdd */
        $userToAdd = User::fromDatabase($db, ['nickname' => $nickname]);

        $checks = [
            'This group does not exist.' => empty($group),
            'This group may not contain members.' => $group ? $group->isModeGroup() : false,
            'This user is not in my current database, in this channel, or is not logged in to services.' =>
                empty($userToAdd) || empty($userToAdd->getIrcAccount()) || in_array($userToAdd->getIrcAccount(),
                    ['*', '0'])
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $group->getUserCollection()
            ->append($userToAdd->getIrcAccount());

        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                sprintf('%s: User %s (identified by %s) has been added to the specified permission group.',
                    $user->getNickname(),
                    $nickname,
                    $userToAdd->getIrcAccount()));
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \WildPHP\Core\StateException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function delmemberCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        $nickname = $args['nickname'];
        $db = Database::fromContainer($this->getContainer());

        /** @var PermissionGroup $group */
        $group = $args['group'];

        /** @var User $userToAdd */
        try {
            $userToAdd = User::fromDatabase($db, ['nickname' => $nickname]);
        } catch (UserNotFoundException $exception) {
        }

        $checks = [
            'This group does not exist.' => empty($group),
            'This user is not in the group or not online.' => empty($userToAdd) || ($group && !$group->getUserCollection()
                        ->contains($userToAdd->getIrcAccount()))
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $group->getUserCollection()
            ->removeAll($userToAdd->getIrcAccount());

        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                sprintf('%s: User %s (identified by %s) has been removed from the specified permission group',
                    $user->getNickname(),
                    $nickname,
                    $userToAdd->getIrcAccount()));
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}