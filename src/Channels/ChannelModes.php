<?php

namespace WildPHP\Core\Channels;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Users\User;

class ChannelModes
{
	protected $definitions = [];

	protected $modeMap = [];

	/**
	 * @var ComponentContainer
	 */
	protected $container = null;

	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);
	}

	public function fetchModeDefinitions()
	{
		$availablemodes = $this->getContainer()->getConfiguration()->get('serverConfig.prefix')->getValue();

		preg_match('/\((.+)\)(.+)/', $availablemodes, $out);

		$modes = str_split($out[1]);
		$prefixes = str_split($out[2]);
		$this->definitions = array_combine($prefixes, $modes);

		$this->getContainer()->getLogger()->debug('Set new mode map', ['map' => $this->definitions]);
	}

	public function setModeDefinitions(array $modemap)
	{
		$this->definitions = $modemap;
	}

	public function getModeDefinitions(): array
	{
		if (empty($this->definitions))
			$this->fetchModeDefinitions();

		return $this->definitions;
	}

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
	 * @return bool
	 */
	public function isBotInMode(string $mode): bool
	{
		if (!array_key_exists($mode, $this->modeMap))
			return false;

		$user = $this->getContainer()->getUserCollection()->getSelf();
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
	public function extractUserModesFromNickname(string $nickname, string &$remainders): array
	{
		$modeMap = $this->getModeDefinitions();
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

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}
}