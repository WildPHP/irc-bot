<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Helpers;

use WildPHP\Core\Helpers\Argumented;
use PHPUnit\Framework\TestCase;

class ArgumentedTest extends TestCase
{

    public function testGetArguments()
    {
        $object = new Argumented('verb', ['arg1']);

        self::assertEquals(['arg1'], $object->getArguments());
    }

    public function testGetVerb()
    {
        $object = new Argumented('verb', ['arg1']);

        self::assertEquals('verb', $object->getVerb());
    }
}
