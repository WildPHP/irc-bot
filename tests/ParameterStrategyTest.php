<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Commands\ParameterStrategy;

class ParameterStrategyTest extends TestCase
{
	public function testConstructInvalidRange()
	{
		self::expectException(InvalidArgumentException::class);
		new ParameterStrategy(5, 2);
	}

	public function testValidateParameter()
	{
		$strategy = new ParameterStrategy(1, 1, [
			'test' => new \WildPHP\Core\Commands\StringParameter()
		]);

		self::assertTrue($strategy->validateParameter('test', 'ing'));

		self::expectException(\Exception::class);
		$strategy->validateParameter('testNonexistant', 'ing');
	}

	public function testValidateArgumentCount()
	{
		$strategy = new ParameterStrategy(1, 1, [
			'test' => new \WildPHP\Core\Commands\StringParameter()
		]);

		$args1 = [
			'test'
		];

		$args2 = [
			'test',
			'ing'
		];

		self::assertTrue($strategy->validateArgumentCount($args1));
		self::assertFalse($strategy->validateArgumentCount($args2));

		$strategy = new ParameterStrategy(-1, -1, [
			'test' => new \WildPHP\Core\Commands\StringParameter()
		], true);

		$args = [];

		$args1 = ['test', 'ing', 'something'];

		self::assertTrue($strategy->validateArgumentCount($args));
		self::assertTrue($strategy->validateArgumentCount($args1));
	}

	public function testImplodeLeftover()
	{
		$strategy = new ParameterStrategy(-1, -1, [
			'test' => new \WildPHP\Core\Commands\StringParameter()
		], true);

		self::assertTrue($strategy->implodeLeftover());

		$args = ['test', 'ing'];

		self::assertEquals(['test ing'], ParameterStrategy::implodeLeftoverArguments($args, 0));

		$validated = $strategy->validateArgumentArray($args);
		self::assertEquals('test ing', $validated['test']);
	}

	public function testValidateArgumentArray()
	{
		$strategy = new ParameterStrategy(3, 3, [
			'test1' => new \WildPHP\Core\Commands\StringParameter(),
			'test2' => new \WildPHP\Core\Commands\StringParameter(),
			'test3' => new \WildPHP\Core\Commands\StringParameter()
		]);

		$args1 = ['foo', 'bar', 'baz'];

		$expected1 = ['test1' => 'foo', 'test2' => 'bar', 'test3' => 'baz'];
		self::assertEquals($expected1, $strategy->validateArgumentArray($args1));
	}

	public function testValidateArgumentArrayCount()
	{
		$strategy = new ParameterStrategy(3, 3, [
			'test1' => new \WildPHP\Core\Commands\StringParameter(),
			'test2' => new \WildPHP\Core\Commands\StringParameter(),
			'test3' => new \WildPHP\Core\Commands\StringParameter()
		]);

		$args = ['foo', 'bar', 'baz', 'test'];

		self::expectException(\InvalidArgumentException::class);
		$strategy->validateArgumentArray($args);
	}


}
