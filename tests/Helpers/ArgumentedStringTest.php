<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Helpers;

use WildPHP\Core\Helpers\ArgumentedString;
use PHPUnit\Framework\TestCase;

class ArgumentedStringTest extends TestCase
{

    public function testIs()
    {
        $string = 'this_is:argumented';

        self::assertTrue(ArgumentedString::is($string));
    }

    public function testIsTooLittleArguments()
    {
        $string = 'this:is:argumented';

        self::assertFalse(ArgumentedString::is($string, 3));
    }

    public function testIsNotArgumented()
    {
        $string = 'this';
        self::assertFalse(ArgumentedString::is($string));
    }

    public function testFromArray()
    {
        $array = [
            'this',
            'is',
            'argumented'
        ];

        $obj = ArgumentedString::fromArray($array);

        self::assertEquals('this', $obj->getVerb());
        self::assertEquals(['is', 'argumented'], $obj->getArguments());
    }

    public function testExtract()
    {
        $obj = ArgumentedString::extract('this:is:argumented');

        self::assertEquals('this', $obj->getVerb());
        self::assertEquals(['is', 'argumented'], $obj->getArguments());
    }
}
