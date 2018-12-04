<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;

use Evenement\EventEmitterInterface;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Database\Database;
use WildPHP\Core\Users\User;
use WildPHP\Messages\RPL\ISupport;
use Yoshi2889\Collections\Collection;

class Validator
{
    /**
     * @var array
     */
    protected $modes = [];

    /**
     * @var string
     */
    protected $owner = '';

    /**
     * @var PermissionGroupCollection
     */
    protected $permissionGroupCollection;

    /**
     * @var Database
     */
    private $database;

    /**
     * Validator constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param PermissionGroupCollection $collection
     * @param Database $database
     * @param string $owner
     */
    public function __construct(EventEmitterInterface $eventEmitter, PermissionGroupCollection $collection, Database $database, string $owner)
    {
        $eventEmitter->on('irc.line.in.005', [$this, 'createModeGroups']);

        $this->permissionGroupCollection = $collection;
        $this->setOwner($owner);
        $this->database = $database;
    }

    /**
     * @param ISupport $ircMessage
     */
    public function createModeGroups(ISupport $ircMessage)
    {
        $variables = $ircMessage->getVariables();

        if (!array_key_exists('prefix', $variables) || !preg_match('/\((.+)\)(.+)/', $variables['prefix'], $out)) {
            return;
        }

        $modes = str_split($out[1]);
        $this->modes = $modes;

        foreach ($this->modes as $mode) {
            if ($this->permissionGroupCollection->offsetExists($mode)) {
                continue;
            }

            $permGroup = new PermissionGroup();
            $permGroup->setModeGroup(true);
            $this->permissionGroupCollection->offsetSet($mode, $permGroup);
        }
    }

    /**
     * @param string $permissionName
     * @param User $user
     * @param Channel|null $channel
     *
     * @return string|false String with reason on success; boolean false otherwise.
     */
    public function isAllowedTo(string $permissionName, User $user, ?Channel $channel = null)
    {
        $db = $this->database;

        // The order to check in:
        // 0. Is bot owner (has all perms)
        // 1. User OP in channel
        // 2. User Voice in channel
        // 3. User in other group with permission
        if ($user->getIrcAccount() == $this->getOwner()) {
            return 'owner';
        }

        if (!empty($channel)) {
            $rows = $db->select('mode_relations', ['mode'],
                ['user_id' => $user->getId(), 'channel_id' => $channel->getId()]);

            foreach ($rows as $row) {
                /** @var PermissionGroup $permissionGroup */
                $permissionGroup = $this->getPermissionGroupCollection()->offsetGet($row['mode']);

                if ($permissionGroup->hasPermission($permissionName)) {
                    return $row['mode'];
                }
            }
        }

        $channelName = !empty($channel) ? $channel->getName() : '';

        /** @var Collection $groups */
        $groups = $this->permissionGroupCollection
            ->filter(function ($item) use ($user) {
                /** @var PermissionGroup $item */
                if ($item->isModeGroup()) {
                    return false;
                }

                return $item->getUserCollection()->contains($user->getIrcAccount());
            });

        foreach ((array)$groups as $name => $group) {
            /** @var PermissionGroup $group */
            if ($group->hasPermission($permissionName, $channelName)) {
                return (string)$name;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return PermissionGroupCollection
     */
    public function getPermissionGroupCollection(): PermissionGroupCollection
    {
        return $this->permissionGroupCollection;
    }

    /**
     * @param PermissionGroupCollection $permissionGroupCollection
     */
    public function setPermissionGroupCollection(PermissionGroupCollection $permissionGroupCollection)
    {
        $this->permissionGroupCollection = $permissionGroupCollection;
    }

    /**
     * @return array
     */
    public function getModes(): array
    {
        return $this->modes;
    }
}