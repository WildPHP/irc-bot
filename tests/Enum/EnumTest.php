<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Enum;

use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{

    public function testToArray()
    {
        $result = TestEnum::toArray();

        $expected = [
            'TEST' => 'TEST',
            'TEST2' => 'TESTING',
            'TEST3' => 'TESTER'
        ];

        $this->assertEquals($expected, $result);
    }
}
