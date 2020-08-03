<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Events;

use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;

class CommandEvent implements EventInterface
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var IrcChannel
     */
    private $channel;

    /**
     * @var IrcUser
     */
    private $user;

    /**
     * @var array
     */
    private $parameters;

    public function __construct(string $command, IrcChannel $channel, IrcUser $user, array $parameters = [])
    {
        $this->command = $command;
        $this->channel = $channel;
        $this->user = $user;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return IrcChannel
     */
    public function getChannel(): IrcChannel
    {
        return $this->channel;
    }

    /**
     * @return IrcUser
     */
    public function getUser(): IrcUser
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
