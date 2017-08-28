<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

use ValidationClosures\Types;
use Yoshi2889\Collections\Collection;

class ParameterStrategy extends Collection
{
	/**
	 * @var int
	 */
	protected $minimumParameters;
	
	/**
	 * @var int
	 */
	protected $maximumParameters;
	
	/**
	 * @var bool
	 */
	protected $implodeLeftover;

	/**
	 * ParameterDefinitions constructor.
	 *
	 * @param int $minimumParameters
	 * @param int $maximumParameters
	 * @param array $initialValues
	 * @param bool $implodeLeftover
	 */
	public function __construct(int $minimumParameters = -1, int $maximumParameters = -1, array $initialValues = [], bool $implodeLeftover = false)
	{
		if ($maximumParameters >= 0 && $minimumParameters > $maximumParameters)
			throw new \InvalidArgumentException('Invalid parameter range (minimum cannot be larger than maximum)');
		
		parent::__construct(Types::instanceof(ParameterInterface::class), $initialValues);
		$this->minimumParameters = $minimumParameters;
		$this->maximumParameters = $maximumParameters;
		$this->implodeLeftover = $implodeLeftover;
	}

	/**
	 * @param string $parameterName
	 * @param string $parameterValue
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function validateParameter(string $parameterName, string $parameterValue)
	{
		if (!$this->offsetExists($parameterName))
			throw new \Exception('Cannot validate value for nonexisting parameter ' . $parameterName);
		
		/** @var ParameterInterface $parameter */
		$parameter = $this[$parameterName];
		
		return $parameter->validate($parameterValue);
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function validateArgumentArray(array $args): array
	{
		$names = array_keys((array) $this);
		
		if (empty($names))
			return $args;
		
		if (!$this->implodeLeftover() && count($args) > count($names))
			throw new \Exception('Parameter count is greater than supported by this definition');
		
		if ($this->implodeLeftover())
		{
			$offset = count($names) - 1;
			$args = self::implodeLeftoverArguments($args, $offset);
		}
		
		$validatedParameters = [];
		foreach ($args as $index => $value)
		{
			if (!array_key_exists($index, $names) || !($return = $this->validateParameter($names[$index], $value)))
				throw new \InvalidArgumentException('Parameter does not validate or index not in range');
			
			if ($return === true)
				$validatedParameters[$names[$index]] = $value;
			
			else
				$validatedParameters[$names[$index]] = $return;
		}
		
		return $validatedParameters;
	}
	
	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	public function validateArgumentCount(array $args): bool
	{
		if ($this->minimumParameters < 0)
			return true;
		
		$count = count($args);
		$maximumParameters = $this->maximumParameters < 0 ? $count : $this->maximumParameters;
		
		return $count >= $this->minimumParameters && $count <= $maximumParameters;
	}

	/**
	 * @return bool
	 */
	public function implodeLeftover(): bool
	{
		return $this->implodeLeftover;
	}

	/**
	 * @param string[] $arguments
	 * @param int $offset
	 *
	 * @return string[]
	 */
	public static function implodeLeftoverArguments(array $arguments, int $offset): array
	{
		$array1 = array_slice($arguments, 0, $offset);
		$array2 = [implode(' ', array_slice($arguments, $offset))];
		return array_merge($array1, $array2);
	}
}