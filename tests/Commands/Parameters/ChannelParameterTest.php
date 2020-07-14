<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Commands\Parameters;

use WildPHP\Core\Commands\Parameters\ChannelParameter;
use PHPUnit\Framework\TestCase;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Storage\IrcChannelStorage;
use WildPHP\Core\Storage\Providers\JsonStorageProvider;
use WildPHP\Core\Storage\Providers\MemoryStorageProvider;

class ChannelParameterTest extends TestCase
{
    /**
     * @var ChannelParameter
     */
    private $testObject;

    protected function setUp(): void
    {
        parent::setUp();

        $channelStorage = new IrcChannelStorage(new MemoryStorageProvider());

        $channelStorage->store(
            new IrcChannel(
                [
                    'name' => '#test'
                ]
            )
        );

        $this->testObject = new ChannelParameter($channelStorage);
    }

    public function testValidate(): void
    {
        self::assertTrue($this->testObject->validate('#test'));
        self::assertFalse($this->testObject->validate('#testing'));
    }

    public function testConvert(): void
    {
        self::assertInstanceOf(
            IrcChannel::class,
            $this->testObject->convert('#test')
        );
        self::assertFalse($this->testObject->convert('#testing'));
    }
}
