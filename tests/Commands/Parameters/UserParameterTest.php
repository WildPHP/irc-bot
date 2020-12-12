<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands\Parameters;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Storage\IrcChannelStorage;
use WildPHP\Core\Storage\IrcUserStorage;
use WildPHP\Core\Storage\Providers\MemoryStorageProvider;

class UserParameterTest extends TestCase
{
    /**
     * @var UserParameter
     */
    private $testObject;

    protected function setUp(): void
    {
        parent::setUp();

        $userStorage = new IrcUserStorage(new MemoryStorageProvider());

        $userStorage->store(
            new IrcUser(
                [
                    'nickname' => 'Test'
                ]
            )
        );

        $this->testObject = new UserParameter($userStorage);
    }

    public function testConvert()
    {
        self::assertInstanceOf(IrcUser::class, $this->testObject->convert('Test'));
        self::assertFalse($this->testObject->convert('Testing'));
    }

    public function testValidate()
    {
        self::assertTrue($this->testObject->validate('Test'));
        self::assertFalse($this->testObject->validate('Testing'));
    }

}
