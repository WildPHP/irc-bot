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
	 * @param CommandHelp|null $commandHelp
	 * @param int $minarguments
	 * @param int $maxarguments
	 * @param string $requiredPermission
	 *
	 * @return Command
	 */
	public static function create(callable $callback,
	                              ?CommandHelp $commandHelp = null,
	                              int $minarguments = -1,
	                              int $maxarguments = -1,
	                              string $requiredPermission = '')
	{
		if (!empty($requiredPermission) && !is_null($commandHelp))
			$commandHelp->append('Required permission: ' . $requiredPermission);

		$commandObject = new Command($callback, $commandHelp, $minarguments, $maxarguments, $requiredPermission);

		return $commandObject;
	}
}