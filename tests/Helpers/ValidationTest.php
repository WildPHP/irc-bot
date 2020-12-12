<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Helpers;

use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Helpers\Argumented;
use WildPHP\Core\Helpers\Validation;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{

    public function testArray()
    {
        self::assertTrue(Validation::array([false, true, false, true, true, false], 'boolean'));
        self::assertFalse(Validation::array([false, true, false, true, true, 10], 'boolean'));
    }

    public function testArrayWithKeys()
    {
        self::assertTrue(Validation::array([10 => false, 11 => true], 'boolean', 'integer'));
        self::assertFalse(Validation::array(['test' => false, 11 => true], 'boolean', 'integer'));
    }

    public function testDefault()
    {
        self::assertEquals('test', Validation::default('test', 'testing'));
        self::assertEquals('testing', Validation::default(null, 'testing'));
    }

    public function testIsClass()
    {
        self::assertTrue(Validation::is(new Argumented('verb', ['arg1']), Argumented::class));
    }

    public function testIsScalar()
    {
        self::assertTrue(Validation::is(10, 'integer'));
    }

    public function testDefaultTypeValue()
    {
        self::assertFalse(Validation::defaultTypeValue('boolean'));
        self::assertEquals(0, Validation::defaultTypeValue('integer'));
        self::assertEquals(0.0, Validation::defaultTypeValue('double'));
        self::assertEquals('', Validation::defaultTypeValue('string'));
        self::assertEquals([], Validation::defaultTypeValue('array'));
        self::assertNull(Validation::defaultTypeValue('null'));
        self::assertNull(Validation::defaultTypeValue('AAAAAAAAAA'));
    }
}
