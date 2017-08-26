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
	 * @var ParameterDefinitions
	 */
	protected $parameterDefinitions;

	/**
	 * Command constructor.
	 *
	 * @param callable $callback
	 * @param ParameterDefinitions $parameterDefinitions
	 * @param null|CommandHelp $commandHelp
	 * @param string $requiredPermission
	 */
	public function __construct(callable $callback, ParameterDefinitions $parameterDefinitions, ?CommandHelp $commandHelp = null, string $requiredPermission = '')
	{
		$this->parameterDefinitions = $parameterDefinitions;
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
	 * @return ParameterDefinitions
	 */
	public function getParameterDefinitions(): ParameterDefinitions
	{
		return $this->parameterDefinitions;
	}

	/**
	 * @param ParameterDefinitions $parameterDefinitions
	 */
	public function setParameterDefinitions(ParameterDefinitions $parameterDefinitions)
	{
		$this->parameterDefinitions = $parameterDefinitions;
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