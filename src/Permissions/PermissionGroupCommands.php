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
use WildPHP\Core\Commands\JoinedChannelParameter;
use WildPHP\Core\Commands\UserParameter;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Database\Database;
use WildPHP\Core\Modules\BaseModule;
use WildPHP\Core\Users\User;

class PermissionGroupCommands extends BaseModule
{
    /**
     * PermissionCommands constructor.
     *
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function __construct(ComponentContainer $container)
    {
        $permissionGroupCollection = PermissionGroupCollection::fromContainer($container);

        CommandRegistrar::fromContainer($container)->register('lsgroups',
            new Command(
                [$this, 'lsgroupsCommand'],
                new ParameterStrategy(0, 0)
            ));

        CommandRegistrar::fromContainer($container)->register('validate',
            new Command(
                [$this, 'validateCommand'],
                [
                    new ParameterStrategy(2, 2, [
                        'user' => new UserParameter(Database::fromContainer($container)),
                        'permission' => new StringParameter()
                    ]),
                    new ParameterStrategy(1, 1, [
                        'permission' => new StringParameter()
                    ])
                ]
            ));

        CommandRegistrar::fromContainer($container)->register('creategroup',
            new Command(
                [$this, 'creategroupCommand'],
                new ParameterStrategy(1, 1, [
                    'groupName' => new StringParameter()
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('delgroup',
            new Command(
                [$this, 'delgroupCommand'],
                new ParameterStrategy(1, 1, [
                    'group' => new ExistingPermissionGroupParameter($permissionGroupCollection)
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('linkgroup',
            new Command(
                [$this, 'linkgroupCommand'],
                new ParameterStrategy(1, 2, [
                    'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
                    'channel' => new JoinedChannelParameter(Database::fromContainer($container))
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('unlinkgroup',
            new Command(
                [$this, 'unlinkgroupCommand'],
                new ParameterStrategy(1, 2, [
                    'group' => new ExistingPermissionGroupParameter($permissionGroupCollection),
                    'channel' => new JoinedChannelParameter(Database::fromContainer($container))
                ])
            ));

        CommandRegistrar::fromContainer($container)->register('groupinfo',
            new Command(
                [$this, 'groupinfoCommand'],
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
    public function validateCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        $valUser = $args['user'] ?? $user;

        $perm = $args['permission'];

        $result = Validator::fromContainer($container)
            ->isAllowedTo($perm, $valUser, $source);

        if ($result) {
            $message = sprintf('%s passes validation for permission "%s" in this context. (permitted by group: %s)',
                $valUser->getNickname(),
                $perm,
                $result);
        } else {
            $message = $valUser->getNickname() . ' does not pass validation for permission "' . $perm . '" in this context.';
        }

        Queue::fromContainer($container)
            ->privmsg($source->getName(), $message);
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function lsgroupsCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var string[] $groups */
        $groups = PermissionGroupCollection::fromContainer($this->getContainer())
            ->keys();

        Queue::fromContainer($container)
            ->privmsg($source->getName(), 'Available groups: ' . implode(', ', $groups));
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function creategroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        $groupName = $args['groupName'];

        $checks = [
            'A group with this name already exists.' => PermissionGroupCollection::fromContainer($container)->offsetExists($groupName)
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $groupObj = new PermissionGroup();
        PermissionGroupCollection::fromContainer($this->getContainer())
            ->offsetSet($groupName, $groupObj);

        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                $user->getNickname() . ': The group "' . $groupName . '" was successfully created.');
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function delgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];

        $checks = [
            'This group may not be removed.' => $group ? $group->isModeGroup() : false
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        PermissionGroupCollection::fromContainer($container)
            ->removeAll($group);

        Queue::fromContainer($container)
            ->privmsg($source->getName(), $user->getNickname() . ': The given group was successfully deleted.');
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function linkgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];

        /** @var Channel $channel */
        $channel = $args['channel'] ?? $source;

        $checks = [
            'This group may not be linked.' => $group ? $group->isModeGroup() : false,
            'The group is already linked to this channel.' => $group ? $group->getChannelCollection()->contains($channel) : false
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $group->getChannelCollection()
            ->append($channel->getName());

        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                $user->getNickname() . ': This group is now linked with channel "' . $channel->getName() . '"');
    }

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function unlinkgroupCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];

        /** @var Channel $channel */
        $channel = $args['channel'] ?? $source;

        $checks = [
            'This group may not be linked.' => $group ? $group->isModeGroup() : false,
            'The group is not linked to this channel.' => $group ? !$group->getChannelCollection()->contains($channel->getName()) : false
        ];

        if (!$this->doChecks($checks, $source, $user)) {
            return;
        }

        $group->getChannelCollection()
            ->removeAll($channel->getName());

        Queue::fromContainer($container)
            ->privmsg($source->getName(),
                $user->getNickname() . ': This group is now no longer linked with channel "' . $channel->getName() . '"');
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param Channel $source
     * @param User $user
     * @param $args
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function groupinfoCommand(Channel $source, User $user, $args, ComponentContainer $container)
    {
        /** @var PermissionGroup $group */
        $group = $args['group'];

        $channels = implode(', ', $group->getChannelCollection()
            ->values());
        $members = implode(', ', $group->getUserCollection()
            ->values());
        $permissions = implode(', ', $group->getAllowedPermissions()
            ->values());

        $lines = [
            'This group is linked to the following channels:',
            $channels,
            'This group contains the following members:',
            $members,
            'This group is allowed the following permissions:',
            $permissions
        ];

        // Avoid sending empty lines.
        $lines = array_filter($lines);

        foreach ($lines as $line) {
            Queue::fromContainer($container)
                ->privmsg($source->getName(), $line);
        }
    }

    /**
     * @return string
     */
    public static function getSupportedVersionConstraint(): string
    {
        return WPHP_VERSION;
    }
}