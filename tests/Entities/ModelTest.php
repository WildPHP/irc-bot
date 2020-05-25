<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Entities;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testCreateModelSetsDefaults()
    {
        $model = new SimpleTestModel();

        $this->assertTrue(isset($model->string));
        $this->assertEquals('Test!', $model->string);
        $this->assertIsArray($model->array);
        $this->assertIsArray($model->simpleArray);
        $this->assertInstanceOf(\DateTime::class, $model->date);

        $this->expectException(InvalidArgumentException::class);
        $model->nonExistantProperty;
    }

    public function testValidateArray()
    {
        $model = new SimpleTestModel();

        $model->array = ['test', 'ing'];

        $this->assertEquals(['test', 'ing'], $model->array);
    }

    public function testToArray()
    {
        $model = new ImmutableTestModel();

        $this->assertEquals(
            [
                'int' => 0,
                'string' => 'Test!',
                'bool' => false
            ],
            $model->toArray()
        );
    }

    public function testAddDefaults()
    {
        $model = new SimpleTestModel(
            [
                'string' => 'Tester!'
            ]
        );

        $this->assertEquals('Tester!', $model->string);
    }

    public function testAddInvalidDefaults()
    {
        $model = new SimpleTestModel(
            [
                'int' => 'string',
                'string' => 6
            ]
        );

        $this->assertNotEquals('string', $model->int);
        $this->assertIsInt($model->int);
        $this->assertNotEquals(6, $model->string);
        $this->assertIsString($model->string);
    }

    public function testAddNonExistantPropertyViaConstructor()
    {
        $model = new SimpleTestModel(
            [
                'test' => 'Tester'
            ]
        );
        $this->assertFalse($model->propertyExists('test'));
    }

    public function testAddNonExistantPropertyViaFill()
    {
        $model = new SimpleTestModel();

        $model->fill(['test' => 'Tester']);
        $this->assertFalse($model->propertyExists('test'));
    }

    public function testAddNonExistantPropertyViaSet()
    {
        $model = new SimpleTestModel();

        $model->test = 'tester';
        $this->assertFalse($model->propertyExists('test'));
    }

    public function testGetNonExistantProperty()
    {
        $model = new SimpleTestModel();

        $this->expectException(InvalidArgumentException::class);
        $model->test;
    }

    public function testModelFill()
    {
        $model = new SimpleTestModel();

        $fill = [
            'string' => 'Tester!',
            'int' => 42,
            'alwaysTrue' => 'haha false'
        ];

        $model->fill($fill);

        // int is not fillable.
        $this->assertNotEquals(42, $model->int);
        $this->assertEquals('Tester!', $model->string);
        $this->assertEquals('haha false', $model->alwaysTrue);

        $model->int = 42;
        $this->assertEquals(42, $model->int);
    }

    public function testSetPropertyWithWrongType()
    {
        $model = new SimpleTestModel();

        $this->assertEquals('Test!', $model->string);

        // immutable model should ignore this call.
        $model->string = 3;
        $this->assertEquals('Test!', $model->string);
    }

    public function testImmutableModel()
    {
        $model = new ImmutableTestModel(
            [
                'string' => 'Testing!'
            ]
        );

        $this->assertEquals('Testing!', $model->string);

        // immutable model should throw a warning on this call
        $this->expectException(InvalidArgumentException::class);
        $model->string = 'Tester!';
        $this->assertEquals('Testing!', $model->string);
    }

    public function testImmutableModelFill()
    {
        $model = new ImmutableTestModel();

        // immutable model should throw a warning on this call
        $this->expectException(InvalidArgumentException::class);
        $model->fill(['string' => 'Tester!']);

        $this->assertNotEquals('Tester!', $model->string);
    }

    public function testCreateMandatoryModelWithoutMandatoryProperties()
    {
        $this->expectException(InvalidArgumentException::class);
        new MandatoryTestModel();
    }
}
