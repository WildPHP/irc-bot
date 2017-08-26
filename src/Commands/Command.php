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
	 * @var int
	 */
	protected $minimumArguments = -1;

	/**
	 * @var int
	 */
	protected $maximumArguments = -1;

	/**
	 * @var string
	 */
	protected $requiredPermission = '';
	
	/**
	 * @var Collection
	 */
	protected $aliasCollection = null;

	/**
	 * Command constructor.
	 *
	 * @param callable $callback
	 * @param null|CommandHelp $commandHelp
	 * @param int $minimumArguments
	 * @param int $maximumArguments
	 * @param string $requiredPermission
	 */
	public function __construct(callable $callback,
	                            ?CommandHelp $commandHelp,
	                            int $minimumArguments = -1,
	                            int $maximumArguments = -1,
	                            string $requiredPermission = '',
								array $aliases = []
	)
	{
		$this->setCallback($callback);
		$this->setMinimumArguments($minimumArguments);
		$this->setMaximumArguments($maximumArguments);
		$this->setHelp($commandHelp);
		$this->setRequiredPermission($requiredPermission);
		$this->setAliasCollection(new Collection(Types::string(), $aliases));
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
	 * @return int
	 */
	public function getMinimumArguments(): int
	{
		return $this->minimumArguments;
	}

	/**
	 * @param int $minimumArguments
	 */
	public function setMinimumArguments(int $minimumArguments)
	{
		$this->minimumArguments = $minimumArguments;
	}

	/**
	 * @return int
	 */
	public function getMaximumArguments(): int
	{
		return $this->maximumArguments;
	}

	/**
	 * @param int $maximumArguments
	 */
	public function setMaximumArguments(int $maximumArguments)
	{
		$this->maximumArguments = $maximumArguments;
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

	/**
	 * @return Collection
	 */
	public function getAliasCollection(): Collection
	{
		return $this->aliasCollection;
	}

	/**
	 * @param Collection $aliasCollection
	 */
	public function setAliasCollection(Collection $aliasCollection)
	{
		$this->aliasCollection = $aliasCollection;
	}
}