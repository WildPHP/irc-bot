<?php

namespace WildPHP\Core\Channels;

use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class ChannelModes
{
	protected static $definitions = [];

	protected $modeMap = [];

	public static function fetchModeDefinitions()
	{
		$availablemodes = Configuration::get('serverConfig.prefix')->getValue();

		preg_match('/\((.+)\)(.+)/', $availablemodes, $out);

		$modes = str_split($out[1]);
		$prefixes = str_split($out[2]);
		self::$definitions = array_combine($prefixes, $modes);

		Logger::debug('Set new mode map', ['map' => self::$definitions]);
	}

	public static function setModeDefinitions(array $modemap)
	{
		self::$definitions = $modemap;
	}

	public static function getModeDefinitions(): array
	{
		if (empty(self::$definitions))
			self::fetchModeDefinitions();

		return self::$definitions;
	}

	public static function getModeNames(): array
	{
		return array_keys(self::$definitions);
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
	 * @return bool
	 */
	public function isBotInMode(string $mode): bool
	{
		if (!array_key_exists($mode, $this->modeMap))
			return false;

		$user = UserCollection::getGlobalSelf();
		return $user ? $this->isUserInMode($mode, $user) : false;
	}


	/**
	 * @param string $mode
	 * @param User[] $users
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
	 * @param string $nickname
	 * @param string $remainders
	 * @return array
	 */
	public static function extractUserModesFromNickname(string $nickname, string &$remainders): array
	{
		$modeMap = self::getModeDefinitions();
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