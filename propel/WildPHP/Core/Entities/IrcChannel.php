<?php

namespace WildPHP\Core\Entities;

use WildPHP\Core\Entities\Base\IrcChannel as BaseIrcChannel;

/**
 * Skeleton subclass for representing a row from the 'channel' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class IrcChannel extends BaseIrcChannel
{
    /**
     * @param IrcUser $user
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findModesForUser(IrcUser $user)
    {
        return UserModeChannelQuery::create()
            ->select('mode')
            ->filterByUserId($user->getId())
            ->filterByChannelId($this->getId())
            ->find();
    }
}
