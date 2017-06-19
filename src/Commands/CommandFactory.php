<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

class CommandFactory
{
	/**
	 * @param callable $callback
	 *
	 * @param CommandHelp|null $commandHelp
	 * @param int $minarguments
	 * @param int $maxarguments
	 * @param string $requiredPermission
	 *
	 * @throws CommandAlreadyExistsException
	 *
	 * @return Command
	 */
	public static function create(callable $callback,
	                              CommandHelp $commandHelp = null,
	                              int $minarguments = -1,
	                              int $maxarguments = -1,
	                              string $requiredPermission = '')
	{
		$commandObject = new Command();
		$commandObject->setCallback($callback);
		$commandObject->setMinimumArguments($minarguments);
		$commandObject->setMaximumArguments($maxarguments);
		$commandObject->setRequiredPermission($requiredPermission);

		if (!empty($requiredPermission) && !is_null($commandHelp))
			$commandHelp->addPage('Required permission: ' . $requiredPermission);

		if (!is_null($commandHelp))
		{
			$commandObject->setHelp($commandHelp);
		}

		return $commandObject;
	}
}