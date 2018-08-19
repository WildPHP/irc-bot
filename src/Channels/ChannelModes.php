<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Channels;

use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Users\User;

class ChannelModes
{
	use ContainerTrait;
	/**
	 * @var array
	 */
	protected $definitions = [];

	/**
	 * @var array
	 */
	protected $modeMap = [];

	/**
	 * @param string $definitions
	 *
	 * @return array
	 */
	protected static function parseDefinitions(string $definitions): array
	{
		if (!preg_match('/\((.+)\)(.+)/', $definitions, $out))
			return [];

		$modes = str_split($out[1]);
		$prefixes = str_split($out[2]);
		return array_combine($prefixes, $modes);
	}

	/**
	 * @return array
	 */
	public function getModeDefinitions(): array
	{
		return $this->definitions;
	}

	/**
	 * @return array
	 */
	public function getModeNames(): array
	{
		return array_keys($this->definitions);
	}

	/**
	 * @param string $mode
	 * @param User $user
	 *
	 * @return bool
	 */
	public function isUserInMode(string $mode, User $user): bool
	{
		if (!array_key_exists($mode, $this->modeMap))
			return false;

		return in_array($user, $this->modeMap[$mode]);
	}


	/**
	 * @param string $mode
	 * @param User[] $users
	 *
	 * @return void
	 */
	public function addUserToMode(string $mode, User ...$users): void
	{
		foreach ($users as $user)
		{
			if ($this->isUserInMode($mode, $user))
			{
				continue;
			}

			$this->modeMap[$mode][] = $user;
		}
	}

	/**
	 * @param string $mode
	 * @param User[] $users
	 *
	 * @internal param User $user
	 *
	 * @return void
	 */
	public function removeUserFromMode(string $mode, User ...$users): void
	{
		foreach ($users as $user)
		{
			if (!$this->isUserInMode($mode, $user))
			{
				continue;
			}

			$key = array_search($user, $this->modeMap[$mode]);
			unset($this->modeMap[$mode][$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getPopulatedModeNames(): array
	{
		return array_keys($this->modeMap);
	}

	/**
	 * @param User $user
	 *
	 * @return array
	 */
	public function getModesForUser(User $user): array
	{
		$modeMap = $this->modeMap;
		$modes = [];
		foreach ($modeMap as $mode => $associatedUsers)
		{
			if (in_array($user, $associatedUsers))
				$modes[] = $mode;
		}

		return $modes;
	}

	/**
	 * @param string $mode
	 *
	 * @return array
	 */
	public function getUsersForMode(string $mode): array
	{
		if (!in_array($mode, $this->getPopulatedModeNames()))
			return [];

		return $this->modeMap[$mode];
	}

	/**
	 * @param array $modes
	 * @param User $user
	 */
	public function addUserToModes(array $modes, User $user)
	{
		foreach ($modes as $mode)
		{
			$this->addUserToMode($mode, $user);
		}
	}

	/**
	 * @param User $user
	 *
	 * @return array List of modes removed.
	 */
	public function removeUserFromAllModes(User $user)
	{
		$modes = $this->getModesForUser($user);
		
		if (empty($modes))
			return [];
		
		foreach ($modes as $mode)
		{
			$this->removeUserFromMode($mode, $user);
		}
		
		return $modes;
	}

	public function wipe()
	{
		$this->modeMap = [];
	}

    /**
     * @param string $prefixes
     * @param string $nickname
     * @param string $remainders
     *
     * @return array
     */
	public static function extractUserModesFromNickname(string $prefixes, string $nickname, string &$remainders): array
	{
		$modeMap = self::parseDefinitions($prefixes);
		$parts = str_split($nickname);
		$modes = [];

		foreach ($parts as $key => $part)
		{
			if (!array_key_exists($part, $modeMap))
			{
				$remainders = join('', $parts);
				break;
			}

			unset($parts[$key]);
			$modes[] = $modeMap[$part];
		}

		return $modes;
	}
}