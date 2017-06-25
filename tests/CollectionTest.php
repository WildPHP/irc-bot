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
	public function testValidateValue()
	{
		$collection = new Collection('string');

		$collection->append('This is a valid string, and should not trigger an exception.');

		$this->expectException(InvalidArgumentException::class);
		// 10 is an int, not a string.
		$collection->append(10);

		$this->expectException(InvalidArgumentException::class);
		// 4.2 is a float/double, not a string.
		$collection->append(4.2);
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
