<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Permissions;

use Evenement\EventEmitterInterface;
use WildPHP\Core\Entities\ModeGroup;
use WildPHP\Core\Entities\PermissionGroup;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Storage\PermissionGroupStorageInterface;
use WildPHP\Core\Storage\PolicyStorageInterface;
use WildPHP\Messages\RPL\ISupport;

class Validator
{
    /**
     * @var string
     */
    protected $owner = '';

    /**
     * @var PolicyStorageInterface
     */
    private $policyStorage;
    /**
     * @var PermissionGroupStorageInterface
     */
    private $groupStorage;

    /**
     * Validator constructor.
     *
     * @param EventEmitterInterface $eventEmitter
     * @param string $owner
     * @param PolicyStorageInterface $policyStorage
     * @param PermissionGroupStorageInterface $groupStorage
     */
    public function __construct(
        EventEmitterInterface $eventEmitter,
        string $owner,
        PolicyStorageInterface $policyStorage,
        PermissionGroupStorageInterface $groupStorage,
        )
    {
        $eventEmitter->on('irc.line.in.005', [$this, 'createModeGroups']);
        $this->setOwner($owner);
        $this->policyStorage = $policyStorage;
        $this->groupStorage = $groupStorage;
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

        foreach ($modes as $mode) {
            if (ModeGroupQuery::create()->findOneByMode($mode) != null) {
                continue;
            }

            $modeGroup = new ModeGroup($mode);
            $modeGroup->save();
        }
    }

    /**
     * @param string $policy
     * @param IrcUser $user
     * @param IrcChannel $channel
     *
     * @return string|false String with reason on success; boolean false otherwise.
     */
    public function isAllowedTo(string $policy, IrcUser $user, IrcChannel $channel)
    {
        // The order to check in:
        // 0. Is bot owner (has all perms)
        // 1. User in mode group with permission
        // 2. User is individually allowed
        // 3. User in other group with permission
        if ($this->isBotOwner($user)) {
            return AllowedBy::BOT_OWNER;
        }

        if ($this->channelModeAllows($policy, $user, $channel)) {
            return AllowedBy::CHANNEL_MODE;
        }

        // save some needless processing, without a valid irc account further validation will fail
        if (!self::userHasValidIrcAccount($user)) {
            return AllowedBy::NONE;
        }

        if ($this->ircAccountAllowsInChannel($policy, $user, $channel)) {
            return AllowedBy::IRC_ACCOUNT;
        }

        $userGroups = UserGroupQuery::create()->findByUserIrcAccount($user->getIrcAccount());

        foreach ($userGroups as $group) {
            if ($this->groupAllowsInChannel($policy, $group, $channel)) {
                return AllowedBy::GROUP;
            }
        }

        return AllowedBy::NONE;
    }

    /**
     * @param IrcUser $user
     * @return bool
     */
    public function isBotOwner(IrcUser $user): bool
    {
        return $user->getIrcAccount() == $this->getOwner();
    }

    /**
     * @param string $policy
     * @param IrcUser $user
     * @return bool
     */
    public function ircAccountAllows(string $policy, IrcUser $user): bool
    {
        if (!self::userHasValidIrcAccount($user)) {
            return false;
        }

        return UserPolicyQuery::create()
                ->filterByUserIrcAccount($user->getIrcAccount())
                ->filterByPolicyName($policy)
                ->findOne() != null;
    }

    /**
     * @param string $policy
     * @param IrcUser $user
     * @param IrcChannel $channel
     * @return bool
     */
    public function ircAccountAllowsInChannel(string $policy, IrcUser $user, IrcChannel $channel): bool
    {
        if (!$this->ircAccountAllows($policy, $user)) {
            return false;
        }

        return UserPolicyRestrictionQuery::create()
                ->filterByUserIrcAccount($user->getIrcAccount())
                ->filterByPolicyName($policy)
                ->filterByChannelId($channel->getId())
                ->findOne() != null;
    }

    /**
     * @param string $policy
     * @param Group $group
     * @return bool
     */
    public function groupAllows(string $policy, Group $group): bool
    {
        return GroupPolicyQuery::create()
                ->filterByGroupId($group->getId())
                ->filterByPolicyName($policy)
                ->findOne() != null;
    }

    /**
     * @param string $policy
     * @param Group $group
     * @param IrcChannel $channel
     * @return bool
     */
    public function groupAllowsInChannel(string $policy, Group $group, IrcChannel $channel): bool
    {
        if (!$this->groupAllows($policy, $group)) {
            return false;
        }

        return GroupPolicyRestrictionQuery::create()
            ->filterByGroupId($group->getId())
            ->filterByPolicyName($policy)
            ->filterByChannelId($channel->getId())
            ->findOne() != null;
    }

    /**
     * @param string $policy
     * @param IrcUser $user
     * @param IrcChannel $channel
     * @return bool
     */
    public function channelModeAllows(string $policy, IrcUser $user, IrcChannel $channel): bool
    {
        $userModes = $channel->findModesForUser($user);

        if (empty($userModes)) {
            return false;
        }

        $validGroupIDs = ModeGroupQuery::create()
            ->select('id')
            ->filterByMode($userModes)
            ->find();

        if (empty($validGroupIDs)) {
            return false;
        }

        return ModeGroupPolicyQuery::create()
                ->filterByModeGroupId($validGroupIDs)
                ->filterByPolicyName($policy)
                ->findOne() != null;
    }

    public static function userHasValidIrcAccount(IrcUser $user)
    {
        return !empty($user->getIrcAccount()) && !in_array($user->getIrcAccount(), ['*', '0']);
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
}