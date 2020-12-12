<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Events;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Events\CommandEvent;

class CommandEventTest extends TestCase
{
    /**
     * @var IrcChannel
     */
    private $channel;

    /**
     * @var IrcUser
     */
    private $user;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string[]
     */
    private $params;

    /**
     * @var CommandEvent
     */
    private $object;


    protected function setUp(): void
    {
        $this->command = 'test';
        $this->channel = new IrcChannel(['name' => '#test']);
        $this->user = new IrcUser(['nickname' => 'Test']);
        $this->params = ['param1'];

        $this->object = new CommandEvent(
            $this->command,
            $this->channel,
            $this->user,
            $this->params
        );
    }

    public function testGetUser()
    {
        self::assertEquals($this->user, $this->object->getUser());
    }

    public function testGetCommand()
    {
        self::assertEquals($this->command, $this->object->getCommand());
    }

    public function testGetChannel()
    {
        self::assertEquals($this->channel, $this->object->getChannel());
    }

    public function testGetParameters()
    {
        self::assertEquals($this->params, $this->object->getParameters());
    }
}
