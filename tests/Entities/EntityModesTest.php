<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Entities;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Entities\EntityModes;

class EntityModesTest extends TestCase
{

    public function test__construct()
    {
        $modes = [
            'o' => true,
            'test' => 'ing'
        ];

        $entityModes = new EntityModes($modes);

        $this->assertEquals($modes, $entityModes->toArray());
    }

    public function testAddMode()
    {
        $entityModes = new EntityModes();

        $entityModes->addMode('v');

        $this->assertEquals(['v' => true], $entityModes->toArray());
    }

    public function testGetModes()
    {
        $entityModes = new EntityModes();

        $entityModes->addMode('v');

        $this->assertEquals(['v'], $entityModes->getModes());
    }

    public function testRemoveMode()
    {
        $entityModes = new EntityModes();

        $entityModes->addMode('v');

        $this->assertEquals(['v' => true], $entityModes->toArray());

        $entityModes->removeMode('v');

        $this->assertEquals([], $entityModes->toArray());
    }

    public function testRemoveModeWhenItDoesntExist()
    {
        $entityModes = new EntityModes();

        $this->expectException(\InvalidArgumentException::class);
        $entityModes->removeMode('v');
    }

    public function testHasMode()
    {
        $entityModes = new EntityModes();

        $entityModes->addMode('v');

        $this->assertTrue($entityModes->hasMode('v'));
    }
}
