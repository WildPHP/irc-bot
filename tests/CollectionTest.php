<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use WildPHP\Core\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
	public function testAppendInvalidValue()
	{
		$collection = new Collection('string');

		$collection->append('This is a valid string, and should not trigger an exception.');

		$this->expectException(InvalidArgumentException::class);
		// 10 is an int, not a string.
		$collection->append(10);

		$this->expectException(InvalidArgumentException::class);
		// 4.2 is a float/double, not a string.
		$collection->append(4.2);

		$this->expectException(InvalidArgumentException::class);
		// is an array, not a string.
		$collection->append([1, 2]);

		$this->expectException(InvalidArgumentException::class);
		// is an object, not a string.
		$collection->append($collection);

		$this->expectException(InvalidArgumentException::class);
		// is a callable or an array, not a string.
		$collection->append([$collection, 'append']);
	}

	public function testValidateValue()
	{
		$types = [
			'string' => 'Test string',
			'int' => 10,
			'float' => 4.2,
			'array' => [1,2],
			'object' => new Collection('string'),
			'callable' => 'is_callable'
		];

		foreach (array_keys($types) as $type)
		{
			$collection = new Collection($type);

			foreach ($types as $typeToTest => $sample)
			{
				if ($collection->getExpectedValueType() == $typeToTest || ($type == 'string' && $typeToTest == 'callable'))
					static::assertTrue($collection->validateType($sample));
				else
					static::assertFalse($collection->validateType($sample));
			}
		}
	}

	public function testRemove()
	{
		$array = [
			'test' => 'ing'
		];
		$collection = new Collection('string', $array);
		$collection->remove('ing');

		static::assertFalse($collection->contains('ing'));
		static::assertFalse($collection->offsetExists('test'));
	}
}
