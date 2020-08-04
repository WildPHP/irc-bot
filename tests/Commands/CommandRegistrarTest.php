<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Commands;

use WildPHP\Commands\Command;
use WildPHP\Commands\CommandProcessor;
use WildPHP\Core\Commands\CommandRegistrar;
use PHPUnit\Framework\TestCase;

class CommandRegistrarTest extends TestCase
{
    public function testRegister()
    {
        $mock = $this->createMock(CommandProcessor::class);

        $object = new CommandRegistrar($mock);

        $command = $this->createMock(Command::class);

        $mock->expects(self::once())
            ->method('registerCommand')
            ->with(
                self::equalTo('test'),
                self::equalTo($command)
            );

        $object->register('test', $command);
    }
}
