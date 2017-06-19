<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Security;

use WildPHP\Core\DataStorage\DataStorageFactory;
use WildPHP\Core\Users\User;

class PermissionGroup
{
	/**
	 * @var string[]
	 */
	protected $userCollection = [];

	/**
	 * @var string[]
	 */
	protected $allowedPermissions = [];

	/**
	 * @var bool
	 */
	protected $canHaveMembers = true;

	/**
	 * @var string[]
	 */
	protected $channels = [];

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * PermissionGroup constructor.
	 *
	 * @param string $name
	 * @param bool $load
	 */
	public function __construct(string $name, $load = false)
	{
		$this->setName($name);

		if ($load)
			$this->loadPermissionsFromStorage();
	}

	/**
	 *
	 */
	public function loadPermissionsFromStorage()
	{
		$dataStorage = DataStorageFactory::getStorage('permissiongroups');

		if (!in_array($this->getName(), $dataStorage->getKeys()))
			return;

		$data = $dataStorage->get($this->getName());
		$this->fromArray($data);
	}

	/**
	 *
	 */
	public function save()
	{
		$dataStorage = DataStorageFactory::getStorage('permissiongroups');

		$dataStorage->set($this->getName(), $this->toArray());
	}

	/**
	 * @param string $ircAccount
	 *
	 * @return bool
	 */
	public function isMemberByIrcAccount(string $ircAccount)
	{
		return in_array($ircAccount, $this->userCollection);
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function isMember(User $user)
	{
		return in_array($user->getIrcAccount(), $this->userCollection);
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function addMember(User $user)
	{
		$ircAccount = $user->getIrcAccount();
		if (empty($ircAccount))
			return false;

		if ($this->isMemberByIrcAccount($ircAccount))
			return true;

		$this->userCollection[] = $ircAccount;

		return true;
	}

	/**
	 * @param string $ircAccount
	 *
	 * @return bool
	 */
	public function addMemberByIrcAccount(string $ircAccount)
	{
		if ($this->isMemberByIrcAccount($ircAccount))
			return true;

		$this->userCollection[] = $ircAccount;

		return true;
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function removeMember(User $user)
	{
		$ircAccount = $user->getIrcAccount();
		if (empty($ircAccount))
			return false;

		if (!$this->isMemberByIrcAccount($ircAccount))
			return true;

		unset($this->userCollection[array_search($ircAccount, $this->userCollection)]);

		return true;
	}

	/**
	 * @param string $ircAccount
	 *
	 * @return bool
	 */
	public function removeMemberByIrcAccount(string $ircAccount)
	{
		if (!$this->isMemberByIrcAccount($ircAccount))
			return true;

		unset($this->userCollection[array_search($ircAccount, $this->userCollection)]);

		return true;
	}

	/**
	 * @param string $channelName
	 *
	 * @return bool
	 */
	public function containsChannel(string $channelName): bool
	{
		return in_array($channelName, $this->channels);
	}

	/**
	 * @param string $channelName
	 *
	 * @return bool
	 */
	public function addChannel(string $channelName): bool
	{
		if ($this->containsChannel($channelName))
			return false;

		$this->channels[] = $channelName;

		return true;
	}

	/**
	 * @param string $channelName
	 *
	 * @return bool
	 */
	public function removeChannel(string $channelName): bool
	{
		if (!$this->containsChannel($channelName))
			return false;

		unset($this->channels[array_search($channelName, $this->channels)]);

		return true;
	}

	/**
	 * @return array
	 */
	public function listChannels(): array
	{
		return $this->channels;
	}

	/**
	 * @return string[]
	 */
	public function getUserCollection(): array
	{
		return $this->userCollection;
	}

	/**
	 * @param array $members
	 */
	protected function setUserCollection(array $members)
	{
		$this->userCollection = $members;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return bool
	 */
	public function getCanHaveMembers(): bool
	{
		return $this->canHaveMembers;
	}

	/**
	 * @param bool $canHaveMembers
	 */
	public function setCanHaveMembers(bool $canHaveMembers)
	{
		$this->canHaveMembers = $canHaveMembers;
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function containsPermission(string $permission): bool
	{
		return in_array($permission, $this->allowedPermissions);
	}

	/**
	 * @param string $permission
	 * @param string $channel
	 *
	 * @return bool
	 */
	public function hasPermission(string $permission, string $channel = ''): bool
	{
		$hasPermission = $this->containsPermission($permission);
		$hasNoChannels = empty($this->listChannels());
		$isCorrectChannel = $hasNoChannels ? true : $this->containsChannel($channel);
		return $hasPermission && $isCorrectChannel;
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function addPermission(string $permission): bool
	{
		if ($this->containsPermission($permission))
			return false;

		$this->allowedPermissions[] = $permission;

		return true;
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function removePermission(string $permission): bool
	{
		if (!$this->containsPermission($permission))
			return false;

		unset($this->allowedPermissions[array_search($permission, $this->allowedPermissions)]);

		return true;
	}

	/**
	 * @return array
	 */
	public function listPermissions(): array
	{
		return $this->allowedPermissions;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'canHaveMembers' => (int) $this->getCanHaveMembers(),
			'members' => $this->getUserCollection(),
			'allowedPermissions' => $this->listPermissions(),
			'linkedChannels' => $this->listChannels(),
		];
	}

	/**
	 * @param array $data
	 */
	public function fromArray(array $data)
	{
		$this->setCanHaveMembers((bool) $data['canHaveMembers']);
		$this->setUserCollection((array) $data['members']);

		$permissions = $data['allowedPermissions'];
		foreach ($permissions as $permission)
		{
			$this->addPermission($permission);
		}

		$channels = $data['linkedChannels'];
		foreach ($channels as $channel)
		{
			$this->addChannel($channel);
		}
	}
}