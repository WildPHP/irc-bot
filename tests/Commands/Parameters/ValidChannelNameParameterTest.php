<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Commands\Parameters;

use WildPHP\Core\Commands\Parameters\ValidChannelNameParameter;
use PHPUnit\Framework\TestCase;

class ValidChannelNameParameterTest extends TestCase
{
    public function testValidate()
    {
        $object = new ValidChannelNameParameter('#');

        self::assertTrue($object->validate('#test'));
        self::assertFalse($object->validate('!test'));
    }

}
