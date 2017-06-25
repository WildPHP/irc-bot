<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core;

class Collection extends \ArrayIterator
{
	/**
	 * @var string
	 */
	protected $expectedValueType = '';

	/**
	 * Collection constructor.
	 *
	 * @param string $expectedValueType
	 * @param array $initialValues
	 */
	public function __construct(string $expectedValueType, array $initialValues = [])
	{
		$this->setExpectedValueType($expectedValueType);

		foreach ($initialValues as $key => $initialValue)
			$this->offsetSet($key, $initialValue);
	}

	/**
	 * @param $value
	 */
	public function append($value)
	{
		if (!$this->validateType($value))
			throw new \InvalidArgumentException('Expected value type for this collection is ' . $this->getExpectedValueType() . ', ' . gettype($value) . ' given.');

		parent::append($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function contains($value): bool
	{
		return in_array($value, (array) $this);
	}

	/**
	 * @param $value
	 *
	 * @return false|int|string|mixed
	 */
	public function getOffset($value)
	{
		return array_search($value, (array) $this);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet($offset, $value)
	{
		if (!$this->validateType($value))
			throw new \InvalidArgumentException('Expected value type for this collection is ' . $this->getExpectedValueType() . ', ' . gettype($value) . ' given.');

		parent::offsetSet($offset, $value);
	}

	/**
	 * @param mixed $value
	 */
	public function remove($value)
	{
		if (!$this->contains($value))
			throw new \InvalidArgumentException('The given value does not exist in this collection.');

		$this->offsetUnset($this->getOffset($value));
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function validateType($value): bool
	{
		$expectedValueType = $this->getExpectedValueType();
		switch ($expectedValueType)
		{
			case 'string':
				return is_string($value);

			case 'int':
			case 'integer':
				return is_int($value);

			case 'float':
			case 'double':
				return is_float($value);

			case 'array':
				return is_array($value);

			case 'object':
				return is_object($value);

			case 'callable':
				return is_callable($value);

			default:
				return ($value instanceof $expectedValueType);
		}
	}

	/**
	 * @return string
	 */
	public function getExpectedValueType(): string
	{
		return $this->expectedValueType;
	}

	/**
	 * @param string $expectedValueType
	 */
	public function setExpectedValueType(string $expectedValueType)
	{
		if (in_array(strtolower($expectedValueType), ['string', 'int', 'integer', 'float', 'double', 'bool', 'boolean', 'array', 'object', 'callable']))
			$expectedValueType = strtolower($expectedValueType);

		$this->expectedValueType = $expectedValueType;
	}
}