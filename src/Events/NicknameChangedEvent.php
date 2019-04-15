<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Events;

use WildPHP\Core\Entities\IrcUser;

class NicknameChangedEvent implements EventInterface
{
    /**
     * @var IrcUser
     */
    private $user;

    /**
     * @var string
     */
    private $oldNickname;

    /**
     * @var string
     */
    private $newNickname;

    /**
     * NicknameChangedEvent constructor.
     * @param IrcUser $user
     * @param string $oldNickname
     * @param string $newNickname
     */
    public function __construct(IrcUser $user, string $oldNickname, string $newNickname)
    {
        $this->user = $user;
        $this->oldNickname = $oldNickname;
        $this->newNickname = $newNickname;
    }

    /**
     * @return IrcUser
     */
    public function getUser(): IrcUser
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getOldNickname(): string
    {
        return $this->oldNickname;
    }

    /**
     * @return string
     */
    public function getNewNickname(): string
    {
        return $this->newNickname;
    }
}
