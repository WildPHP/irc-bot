<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

class Command
{
	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var CommandHelp|null
	 */
	protected $help = null;

	/**
	 * @var string
	 */
	protected $requiredPermission = '';

	/**
	 * @var ParameterStrategy[]
	 */
	protected $parameterStrategies;

	/**
	 * Command constructor.
	 *
	 * @param callable $callback
	 * @param array|ParameterStrategy $parameterStrategies
	 * @param null|CommandHelp $commandHelp
	 * @param string $requiredPermission
	 */
	public function __construct(callable $callback, $parameterStrategies, ?CommandHelp $commandHelp = null, string $requiredPermission = '')
	{
		if (!is_array($parameterStrategies))
			$parameterStrategies = [$parameterStrategies];
		
		$this->parameterStrategies = $parameterStrategies;
		$this->callback = $callback;
		$this->help = $commandHelp;
		$this->requiredPermission = $requiredPermission;
	}

	/**
	 * @return callable
	 */
	public function getCallback(): callable
	{
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @return array
	 */
	public function getParameterStrategies(): array
	{
		return $this->parameterStrategies;
	}

	/**
	 * @param ParameterStrategy[] $parameterStrategies
	 */
	public function setParameterStrategies(array $parameterStrategies)
	{
		$this->parameterStrategies = $parameterStrategies;
	}

	/**
	 * @return CommandHelp|null
	 */
	public function getHelp(): ?CommandHelp
	{
		return $this->help;
	}

	/**
	 * @param null|CommandHelp $help
	 */
	public function setHelp(?CommandHelp $help)
	{
		$this->help = $help;
	}

	/**
	 * @return string
	 */
	public function getRequiredPermission(): string
	{
		return $this->requiredPermission;
	}

	/**
	 * @param string $requiredPermission
	 */
	public function setRequiredPermission(string $requiredPermission)
	{
		$this->requiredPermission = $requiredPermission;
	}
}