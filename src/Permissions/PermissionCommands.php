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
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandRegistrar;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class PermissionCommands extends BaseModule
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

        CommandRegistrar::fromContainer($container)->register('allow',
            new Command(
                [$this, 'allowCommand'],
                new ParameterStrategy(2, 2, [
                    'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
                    'permission' => new StringParameter()
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('deny',
            new Command(
                [$this, 'denyCommand'],
                new ParameterStrategy(2, 2, [
                    'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
                    'permission' => new StringParameter()
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('lsperms',
            new Command(
                [$this, 'lspermsCommand'],
                new ParameterStrategy(1, 1, [
                    'group' => new ExistingPermissionGroupParameter($permissionGroupCollection)
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
    public function allowCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];
        $permission = $args['permission'];

        $checks = [
            'This group is already allowed to do that.' => $group ? $group->getAllowedPermissions()->contains($permission) : false
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $group->getAllowedPermissions()
            ->append($permission);
        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                $user->getNickname() . ': This group is now allowed the permission "' . $permission . '"');
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function denyCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];
        $permission = $args['permission'];

        $checks = [
            'This group is not allowed to do that.' => $group ? !$group->getAllowedPermissions()->contains($permission) : false
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $group->getAllowedPermissions()
            ->removeAll($permission);
        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                $user->getNickname() . ': This group is now denied the permission "' . $permission . '"');
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function lspermsCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];

        $perms = $group->getAllowedPermissions()
            ->values();
        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                $user->getNickname() . ': The following permissions are set for this group: ' . implode(', ', $perms));
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}